<?php

namespace P4\MasterTheme;

/**
 * Class NavMenuCache
 *
 * Optimizes DB queries triggered by nav menus.
 *
 * Planet4 renders menus through Timber/Twig (no wp_nav_menu() calls), so the
 * cost concentrates in:
 *   1. wp_get_nav_menu_items() — runs a WP_Query for nav_menu_item posts plus
 *      per-item meta and term lookups during wp_setup_nav_menu_item().
 *   2. wp_get_object_terms() — called repeatedly for the same item by both
 *      core's setup and Timber's processing.
 *
 * This class implements:
 *   - Cached array result of wp_get_nav_menu_items() (per menu term + version).
 *   - Bulk-priming of post-meta and object-term caches for the items returned,
 *     so Timber's downstream processing reads from the object cache instead of
 *     hitting the DB once per item.
 *   - Per-request memoization of wp_get_object_terms() to dedupe identical
 *     calls within the same request.
 *   - A single integer cache version that's bumped on any menu mutation —
 *     keys embed the version, so old entries become unreachable and Redis
 *     evicts them naturally (no flush storms).
 */
class NavMenuCache
{
    /**
     * Object cache group used when an external object cache is present.
     * The numeric suffix is bumped when the cache *format* changes (e.g.
     * stdClass-vs-WP_Post). Bumping it invalidates every previously-stored
     * entry without needing a manual flush.
     */
    private const CACHE_GROUP = 'p4_nav_menu_v4';

    /**
     * Option name that stores the integer cache version.
     */
    private const VERSION_OPTION = 'p4_nav_menu_cache_version';

    /**
     * Default TTL for cached entries (12 hours). Invalidation is version-based,
     * so this is a safety ceiling, not the primary lifetime control.
     */
    private const TTL = 12 * HOUR_IN_SECONDS;

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
        // Bypass everything in admin/customize/preview to keep menu editing fresh.
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST && $this->is_menu_rest_request())) {
            $this->register_invalidation_hooks();
            return;
        }

        // Layer B is intentionally NOT a wp_get_nav_menu_items filter.
        // wp_setup_nav_menu_item() runs BEFORE that filter, so any "priming"
        // we do at filter time is too late — per-item meta has already been
        // queried. And calling set_transient() on every request fires a
        // wp_options UPDATE per cached menu, adding queries instead of
        // saving them. Caching now lives entirely in NavMenuCache::get_items()
        // (below), used by callers that want to bypass wp_get_nav_menu_items()
        // on warm cache (the 3 footer menus in MasterSite.php).

        // Layer D — dedupe wp_get_object_terms within a request.
        add_filter('pre_wp_get_object_terms', [$this, 'dedupe_object_terms_pre'], 10, 4);
        add_filter('wp_get_object_terms', [$this, 'dedupe_object_terms_capture'], 999);

        // Note: an earlier "Layer A" hooked pre_wp_nav_menu / wp_nav_menu as
        // defensive output caching, but P4 doesn't call wp_nav_menu()
        // and Timber\Menu::__toString() invokes walk_nav_menu_tree() which
        // triggers those filters in unexpected ways. The hooks are intentionally
        // omitted so the class only acts on wp_get_nav_menu_items / wp_get_object_terms.

        $this->register_invalidation_hooks();
    }

    /**
     * Whether a persistent external object cache (Redis) is active.
     */
    private static function using_persistent_cache(): bool
    {
        return function_exists('wp_using_ext_object_cache') && wp_using_ext_object_cache();
    }

    /**
     * Read a cached value. Returns null on miss.
     *
     * @return mixed
     */
    private static function cache_get(string $key)
    {
        if (self::using_persistent_cache()) {
            $found = false;
            $value = wp_cache_get($key, self::CACHE_GROUP, false, $found);
            return $found ? $value : null;
        }
        $value = get_transient(self::CACHE_GROUP . '_' . $key);
        return false === $value ? null : $value;
    }

    /**
     * Write a cached value, but only if the key is not already populated.
     *
     * Without this guard, set_transient() writes wp_options on every request
     * even when the value is unchanged — turning the cache into a query
     * amplifier. With the guard, transient writes only happen on cache miss.
     *
     * @param mixed $value
     */
    private static function cache_set(string $key, $value): bool
    {
        if (self::using_persistent_cache()) {
            $found = false;
            wp_cache_get($key, self::CACHE_GROUP, false, $found);
            if ($found) {
                return true;
            }
            return (bool) wp_cache_set($key, $value, self::CACHE_GROUP, self::TTL);
        }
        if (false !== get_transient(self::CACHE_GROUP . '_' . $key)) {
            return true;
        }
        return (bool) set_transient(self::CACHE_GROUP . '_' . $key, $value, self::TTL);
    }

    /**
     * Current cache version (used as part of every key).
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

        // Drop the per-request cache too so the next call within this request doesn't return stale data.
        self::$items_request_cache = [];
        self::$terms_request_cache = [];
        self::$terms_pending_signature = null;
    }

    /* ---------------------------------------------------------------------
     *  Layer B — cache wp_get_nav_menu_items()
     * --------------------------------------------------------------------- */

    /**
     * Drop-in replacement for wp_get_nav_menu_items() that consults our cache
     * BEFORE running the underlying WP_Query.
     *
     * On a warm cache:
     *   - Returns the cached array with zero DB queries beyond resolving the
     *     menu term object (which is itself cached by core in the `terms` /
     *     `nav_menu` cache groups).
     *   - Primes related caches so any subsequent reads stay in object cache.
     *
     * On a cold cache: delegates to wp_get_nav_menu_items(), which then runs
     * cache_and_prime_items() via the wp_get_nav_menu_items filter.
     *
     * @param int|string|\WP_Term $menu_identifier Menu ID, slug, name, or term object.
     * @param array               $args            Same shape as wp_get_nav_menu_items() $args.
     * @return array|false Items array, or false if the menu is missing.
     */
    public static function get_items($menu_identifier, array $args = [])
    {
        // Resolve the menu term once. wp_get_nav_menu_object is cheap (cached
        // by core under the `nav_menu` term cache group on warm cache).
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

        // Cold cache — delegate to core, then store the result.
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
     * The key normalizes $args against core's defaults so the same key is
     * produced whether build_items_key() is called from:
     *   - get_items() with raw user args, or
     *   - the wp_get_nav_menu_items filter (where core has merged defaults).
     *
     * Vary on:
     *   - cache version (global invalidation)
     *   - menu term_id
     *   - normalized args hash (canonical order, defaults applied)
     *   - language (WPML — different items per locale)
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

        // Mirror the defaults that wp_get_nav_menu_items() merges internally,
        // then canonicalize key order so the hash is stable.
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
            'items_v%d_%d_%s_%s',
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
     * properties added at runtime (->url, ->title, ->target, ->classes, etc.).
     * stdClass has no __sleep, so all properties are preserved.
     *
     */
    private static function items_for_cache(array $items): array
    {
        $out = [];
        foreach ($items as $key => $item) {
            $out[$key] = is_object($item) ? (object) get_object_vars($item) : $item;
        }
        return $out;
    }

    /* ---------------------------------------------------------------------
     *  Layer D — dedupe wp_get_object_terms within a request
     * --------------------------------------------------------------------- */

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

    /* ---------------------------------------------------------------------
     *  Invalidation
     * --------------------------------------------------------------------- */

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
