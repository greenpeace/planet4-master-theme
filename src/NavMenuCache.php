<?php

namespace P4\MasterTheme;

/**
 * Class NavMenuCache
 *
 * Reduces DB queries triggered by nav menus.
 *
 * Planet4 renders menus through Timber/Twig (no wp_nav_menu() calls), so the
 * cost concentrates in two places:
 *   1. wp_get_nav_menu_items() — runs a WP_Query for nav_menu_item posts and
 *      per-item meta lookups via wp_setup_nav_menu_item().
 *   2. wp_get_object_terms() — called repeatedly for the same menu item by
 *      core's setup and Timber's processing.
 *
 * This class implements:
 *   - Provides NavMenuCache::get_items(), a drop-in replacement for
 *     wp_get_nav_menu_items() that consults the object cache first. Used by
 *     MasterSite::add_to_context() for the footer menus.
 *   - Dedupes wp_get_object_terms() within a single request via in-memory
 *     memoization.
 *   - Bumps a cache-version option on every menu mutation; cache keys embed
 *     the version, so old entries become unreachable and Redis evicts them
 *     naturally (no flush storms, no manual cleanup).
 */
class NavMenuCache
{
    /**
     * Object cache group.
     */
    private const CACHE_GROUP = 'p4-cache-nav-menu';

    /**
     * Option name that stores the integer cache version.
     */
    private const VERSION_OPTION = 'p4-nav-menu-cache-version';

    /**
     * Default TTL for cached entries (24 hours).
     */
    private const TTL = DAY_IN_SECONDS;

    /**
     * In-memory cache of wp_get_nav_menu_items() results for this request.
     *
     * @var array<string, array>
     */
    private static array $items_request_cache = [];

    /**
     * In-memory cache of wp_get_object_terms() results for this request.
     *
     * @var array<string, mixed>
     */
    private static array $terms_request_cache = [];

    /**
     * Signature of the wp_get_object_terms() call currently in flight.
     */
    private static ?string $terms_pending_signature = null;

    /**
     * Constructor — registers all filters/actions.
     */
    public function __construct()
    {
        // Bypass cache reads in admin / menu REST endpoints to keep menu editing fresh.
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST && $this->is_menu_rest_request())) {
            $this->register_invalidation_hooks();
            return;
        }

        // Dedupe wp_get_object_terms() within a single request.
        add_filter('pre_wp_get_object_terms', [$this, 'dedupe_object_terms_pre'], 10, 4);
        add_filter('wp_get_object_terms', [$this, 'dedupe_object_terms_capture'], 999);

        $this->register_invalidation_hooks();
    }

    /**
     * Read a cached value. Returns null on miss.
     *
     * @return mixed
     */
    private static function cache_get(string $key)
    {
        $found = false;
        $value = wp_cache_get($key, self::CACHE_GROUP, false, $found);
        return $found ? $value : null;
    }

    /**
     * Write a cached value, but only if the key is not already populated.
     *
     * @param mixed $value
     */
    private static function cache_set(string $key, $value): bool
    {
        return (bool) wp_cache_add($key, $value, self::CACHE_GROUP, self::TTL);
    }

    /**
     * Current cache version (used as part of every cache key).
     */
    private static function cache_version(): int
    {
        $version = (int) get_option(self::VERSION_OPTION, 0);
        if ($version <= 0) {
            $version = 1;
            update_option(self::VERSION_OPTION, $version, false);
        }
        return $version;
    }

    /**
     * Bump the cache version, instantly invalidating every cached entry.
     */
    public function invalidate(): void
    {
        $version = self::cache_version() + 1;
        update_option(self::VERSION_OPTION, $version, false);

        // Drop the per-request cache so the next call in this request doesn't return stale data.
        self::$items_request_cache = [];
        self::$terms_request_cache = [];
        self::$terms_pending_signature = null;
    }

    /**
     * Drop-in replacement for wp_get_nav_menu_items() that consults our cache
     * BEFORE running the underlying WP_Query.
     *
     * On a warm cache: returns the cached array, skipping the WP_Query and
     * per-item meta lookups inside wp_setup_nav_menu_item().
     *
     * On a cold cache: delegates to wp_get_nav_menu_items() and stores the
     * result for next time.
     *
     * @param int|string|\WP_Term $menu_identifier Menu ID, slug, name, or term object.
     * @param array               $args            Same shape as wp_get_nav_menu_items() $args.
     * @return array|false Items array, or false if the menu is missing.
     */
    public static function get_items($menu_identifier, array $args = [])
    {
        $menu = wp_get_nav_menu_object($menu_identifier);
        if (!$menu || empty($menu->term_id)) {
            return false;
        }

        $key = self::build_items_key($menu, $args);

        if (isset(self::$items_request_cache[$key])) {
            return self::$items_request_cache[$key];
        }

        $cached = self::cache_get($key);
        if (is_array($cached)) {
            self::$items_request_cache[$key] = $cached;
            return $cached;
        }

        $items = wp_get_nav_menu_items($menu, $args);
        if (is_array($items)) {
            self::$items_request_cache[$key] = $items;
            self::cache_set($key, self::items_for_cache($items));
        }
        return $items;
    }

    /**
     * Build the cache key for a given menu lookup.
     *
     * Vary on cache version (global invalidation), menu term_id, normalized
     * args hash (defaults applied + canonical order), and language.
     *
     * $args is normalized against the defaults wp_get_nav_menu_items()
     * applies internally, so build_items_key() produces the same key
     * regardless of whether the caller passed [] or the merged defaults.
     *
     * @param \WP_Term|object $menu Menu term object (must expose ->term_id).
     * @param array           $args wp_get_nav_menu_items() args.
     */
    private static function build_items_key($menu, array $args): string
    {
        $menu_id = is_object($menu) && isset($menu->term_id) ? (int) $menu->term_id : 0;

        $lang = '';
        if (function_exists('pll_current_language')) {
            $lang = (string) pll_current_language();
        } elseif (defined('ICL_LANGUAGE_CODE')) {
            $lang = (string) ICL_LANGUAGE_CODE;
        }

        $defaults = [
            'order' => 'ASC',
            'orderby' => 'menu_order',
            'post_type' => 'nav_menu_item',
            'post_status' => 'publish',
            'output' => ARRAY_A,
            'output_key' => 'menu_order',
            'nopaging' => true,
        ];
        $normalized = array_merge($defaults, $args);
        $normalized['include'] = $normalized['include'] ?? null;
        ksort($normalized);

        return sprintf(
            'items-v%d-%d-%s-%s',
            self::cache_version(),
            $menu_id,
            $lang,
            md5((string) wp_json_encode($normalized))
        );
    }

    /**
     * Convert nav menu items to plain stdClass so they survive serialization.
     *
     * WP_Post::__sleep() only serializes the canonical post columns, dropping
     * properties added at runtime by wp_setup_nav_menu_item() (->url, ->title,
     * ->target, ->classes, etc.). stdClass has no __sleep, so all properties
     * are preserved through the cache round-trip.
     */
    private static function items_for_cache(array $items): array
    {
        $out = [];
        foreach ($items as $key => $item) {
            $out[$key] = is_object($item) ? (object) get_object_vars($item) : $item;
        }
        return $out;
    }

    /**
     * Pre-filter: serve from in-memory cache, or mark this signature pending.
     *
     * @param mixed $pre
     * @return mixed
     */
    public function dedupe_object_terms_pre($pre, array $object_ids, array $taxonomies, array $args)
    {
        if (null !== $pre) {
            return $pre;
        }

        $signature = md5((string) wp_json_encode([$object_ids, $taxonomies, $args]));

        if (array_key_exists($signature, self::$terms_request_cache)) {
            return self::$terms_request_cache[$signature];
        }

        self::$terms_pending_signature = $signature;
        return null;
    }

    /**
     * Post-filter: store the result for whichever signature is in flight.
     *
     * @param mixed $terms
     * @return mixed
     */
    public function dedupe_object_terms_capture($terms)
    {
        if (null !== self::$terms_pending_signature) {
            self::$terms_request_cache[self::$terms_pending_signature] = $terms;
            self::$terms_pending_signature = null;
        }
        return $terms;
    }

    private function register_invalidation_hooks(): void
    {
        // Menu structure changed in admin / Customizer.
        add_action('wp_update_nav_menu', [$this, 'invalidate']);
        add_action('wp_update_nav_menu_item', [$this, 'invalidate']);
        add_action('wp_delete_nav_menu', [$this, 'invalidate']);
        add_action('customize_save_after', [$this, 'invalidate']);
        add_action('switch_theme', [$this, 'invalidate']);

        // A nav_menu_item post being deleted directly.
        add_action('delete_post', function (int $post_id): void {
            if (get_post_type($post_id) !== 'nav_menu_item') {
                return;
            }

            $this->invalidate();
        });

        // WPML — language switches & sync.
        add_action('icl_make_duplicate', [$this, 'invalidate']);
    }

    /**
     * Detect whether the current REST request targets the menus endpoint.
     */
    private function is_menu_rest_request(): bool
    {
        $route = $_SERVER['REQUEST_URI'] ?? '';
        if (!is_string($route) || $route === '') {
            return false;
        }
        return str_contains($route, '/wp/v2/menus')
            || str_contains($route, '/wp/v2/menu-items')
            || str_contains($route, '/wp/v2/menu-locations');
    }
}
