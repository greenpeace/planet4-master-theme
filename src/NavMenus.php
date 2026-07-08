<?php

namespace P4\MasterTheme;

use Timber\Helper as TimberHelper;
use Timber\Timber;

/**
 * Class NavMenus
 *
 * Centralises nav-menus functionality for the theme:
 *  - registers the 5 menu locations (top nav, donate, 3x footer)
 *  - serves menu items from 24h Timber transients, with invalidation on any menu mutation
 *  - resets per-request current-page state on the top nav and donate menu so cached items track the page being rendered
 *  - varies cache keys by language on WPML sites
 *  - applies the transparent-nav body class on the front page
 */
class NavMenus
{
    private const FOOTER_SOCIAL_KEY = 'p4_footer_social_menu';
    private const FOOTER_PRIMARY_KEY = 'p4_footer_primary_menu';
    private const FOOTER_SECONDARY_KEY = 'p4_footer_secondary_menu';
    private const NAVBAR_KEY = 'p4_navbar_menu_items';
    private const DONATE_KEY = 'p4_donate_menu_items';

    /**
     * CSS classes that reflect per-request "current page" state on menu items. Stripped from cached entries and re-added
     * at read time by recompute_current_state() based on the URL being rendered.
     */
    private const STALE_CURRENT_CLASSES = [
        'current-menu-item',
        'current-menu-parent',
        'current-menu-ancestor',
        'current_page_item',
        'current_page_parent',
        'current_page_ancestor',
    ];

    public function __construct()
    {
        add_action('after_setup_theme', [self::class, 'register_locations'], 0);

        $invalidate = [self::class, 'invalidate_menu_cache'];
        add_action('wp_update_nav_menu', $invalidate);
        add_action('wp_update_nav_menu_item', $invalidate);
        add_action('wp_delete_nav_menu', $invalidate);
        add_action('customize_save_after', $invalidate);

        add_filter('body_class', [self::class, 'add_transparent_nav_class']);
    }

    /**
     * Register the 5 nav-menu locations the theme uses.
     */
    public static function register_locations(): void
    {
        register_nav_menus([
            'navigation-bar-menu' => __('Navigation Bar Menu', 'planet4-master-theme-backend'),
            'donate-menu' => __('Donate Button', 'planet4-master-theme-backend'),
            'footer-primary-menu' => __('Footer Primary Menu', 'planet4-master-theme-backend'),
            'footer-secondary-menu' => __('Footer Secondary Menu', 'planet4-master-theme-backend'),
            'footer-social-menu' => __('Footer Social Menu', 'planet4-master-theme-backend'),
        ]);
    }

    /**
     * Cached items for the footer-social-menu location.
     */
    public static function footer_social_menu_items(): array
    {
        return self::cached_items(self::FOOTER_SOCIAL_KEY, 'footer-social-menu', 'Footer Social');
    }

    /**
     * Cached items for the footer-primary-menu location.
     */
    public static function footer_primary_menu_items(): array
    {
        return self::cached_items(self::FOOTER_PRIMARY_KEY, 'footer-primary-menu', 'Footer Primary');
    }

    /**
     * Cached items for the footer-secondary-menu location.
     */
    public static function footer_secondary_menu_items(): array
    {
        return self::cached_items(self::FOOTER_SECONDARY_KEY, 'footer-secondary-menu', 'Footer Secondary');
    }

    /**
     * Cached Timber\MenuItem[] for the navigation-bar-menu location.
     *
     * The wpml-ls-item language switcher entries are stripped at cache-write time. Current-menu-item state is stripped
     * from the cache and recomputed per request in recompute_current_state() so the active highlight tracks the page
     * being rendered, not the page that warmed the cache.
     */
    public static function navbar_menu_items(): array
    {
        $items = self::cached_timber_menu_items(
            self::NAVBAR_KEY,
            'navigation-bar-menu',
            static fn(array $items): array => array_values(array_filter(
                $items,
                static fn($item) => !in_array('wpml-ls-item', $item->classes ?? [], true)
            ))
        );

        self::recompute_current_state($items);

        return $items;
    }

    /**
     * Cached Timber\MenuItem[] for the donate-menu location.
     *
     * Current-menu-item state is stripped from the cache and recomputed per request in recompute_current_state().
     */
    public static function donate_menu_items(): array
    {
        $items = self::cached_timber_menu_items(self::DONATE_KEY, 'donate-menu');
        self::recompute_current_state($items);
        return $items;
    }

    /**
     * Delete every nav-menu transient. Called on any nav-menu mutation.
     *
     * On multilingual sites each language has its own cache entry, so we enumerate active language codes and delete
     * every variant. Also deletes the un-suffixed base keys for safety on sites where the plugin is inactive.
     */
    public static function invalidate_menu_cache(): void
    {
        $base_keys = [
            self::FOOTER_SOCIAL_KEY,
            self::FOOTER_PRIMARY_KEY,
            self::FOOTER_SECONDARY_KEY,
            self::NAVBAR_KEY,
            self::DONATE_KEY,
        ];

        $suffixes = array_merge([''], array_map(
            static fn(string $lang): string => '_' . $lang,
            self::active_language_codes()
        ));

        foreach ($base_keys as $key) {
            foreach ($suffixes as $suffix) {
                delete_transient($key . $suffix);
            }
        }
    }

    /**
     * Add the `transparent-nav` body class on the front page when enabled.
     */
    public static function add_transparent_nav_class(array $classes): array
    {
        if (is_front_page() && !empty(planet4_get_option('transparent_nav'))) {
            $classes[] = 'transparent-nav';
        }
        return $classes;
    }

    /**
     * Get a footer menu's items, served from a 24h transient.
     *
     * The cache key is suffixed with the current language code (for WPML) so each language has its own cache entry,
     * otherwise whichever language warms the cache first would leak into the other languages' rendering.
     */
    private static function cached_items(string $cache_key, string $location, string $fallback): array
    {
        $key = self::localised_key($cache_key);

        $items = TimberHelper::transient(
            $key,
            static fn() => self::resolve_items($location, $fallback),
            DAY_IN_SECONDS
        );

        return is_array($items) ? $items : [];
    }

    /**
     * Fetch and cache fully-wrapped Timber\MenuItem[] for a menu location. Used for the navigation-bar-menu and
     * donate-menu, whose templates rely on Timber\MenuItem accessors (`.link`, `.children`, `.title`) that a plain
     * WP_Post array doesn't provide.
     *
     * The optional $post_filter runs once at cache-write time and is responsible for stripping items that shouldn't
     * be persisted (e.g. the WPML language switcher entries on the top nav).
     *
     * @param callable(array):array|null $post_filter
     */
    private static function cached_timber_menu_items(
        string $cache_key,
        string $location,
        ?callable $post_filter = null
    ): array {
        $key = self::localised_key($cache_key);

        $items = TimberHelper::transient(
            $key,
            static function () use ($location, $post_filter): array {
                if (!has_nav_menu($location)) {
                    return [];
                }
                $menu = Timber::get_menu($location);
                if (!$menu) {
                    return [];
                }
                $items = $menu->get_items();
                if (!is_array($items)) {
                    return [];
                }

                if ($post_filter !== null) {
                    $items = $post_filter($items);
                }

                // Strip per-request state so the cached copy is safe to return on any URL.
                // recompute_current_state() reapplies it at read time.
                self::strip_current_state($items);

                return $items;
            },
            DAY_IN_SECONDS
        );

        return is_array($items) ? $items : [];
    }

    /**
     * Zero out the ->current / ->current_item_* flags and remove the current-menu-* CSS classes before caching, so no
     * page-specific state leaks into the cached representation.
     *
     * @param array $items Timber\MenuItem-like objects (recursed via ->children).
     */
    private static function strip_current_state(array $items): void
    {
        foreach ($items as $item) {
            if (!is_object($item)) {
                continue;
            }

            $item->current = false;
            $item->current_item_ancestor = false;
            $item->current_item_parent = false;

            if (isset($item->classes) && is_array($item->classes)) {
                $item->classes = array_values(array_diff($item->classes, self::STALE_CURRENT_CLASSES));
            }

            if (!empty($item->children) && is_array($item->children)) {
                self::strip_current_state($item->children);
            }
        }
    }

    /**
     * Reapply current-menu-* state on cached items based on the URL being rendered. Matches item URL against the
     * current request URL; ancestor / parent detection is left to Twig template logic (Planet4 templates only use
     * ->current for the active class).
     *
     * @param array $items Timber\MenuItem-like objects (recursed via ->children).
     */
    private static function recompute_current_state(array $items): void
    {
        if (empty($items)) {
            return;
        }

        global $wp;
        $current_url = isset($wp->request)
            ? untrailingslashit(home_url($wp->request))
            : untrailingslashit(home_url((string) ($_SERVER['REQUEST_URI'] ?? '')));

        foreach ($items as $item) {
            if (!is_object($item)) {
                continue;
            }

            $item_url = self::item_url($item);
            $is_current = $item_url !== '' && untrailingslashit($item_url) === $current_url;

            $item->current = $is_current;
            if ($is_current) {
                $item->classes = isset($item->classes) && is_array($item->classes)
                    ? array_values(array_unique(array_merge($item->classes, ['current-menu-item'])))
                    : ['current-menu-item'];
            }

            if (!empty($item->children) && is_array($item->children)) {
                self::recompute_current_state($item->children);
            }
        }
    }

    /**
     * Best-effort URL accessor for a Timber\MenuItem-like object. Timber exposes both ->link (method) and ->url
     * (property) depending on version; fall back to either.
     */
    private static function item_url(object $item): string
    {
        if (isset($item->url) && is_string($item->url) && $item->url !== '') {
            return $item->url;
        }
        if (method_exists($item, 'link')) {
            $link = $item->link();
            if (is_string($link) && $link !== '') {
                return $link;
            }
        }
        if (isset($item->link) && is_string($item->link) && $item->link !== '') {
            return $item->link;
        }
        return '';
    }

    /**
     * Append the current language code to a base cache key so each language on a WPML site has its own entry.
     */
    private static function localised_key(string $base_key): string
    {
        $lang = self::current_language_code();
        return $lang !== '' ? $base_key . '_' . $lang : $base_key;
    }

    /**
     * Get current language code for cache-key variance. Returns empty string when WPML is not active.
     */
    private static function current_language_code(): string
    {
        if (defined('ICL_LANGUAGE_CODE')) {
            return (string) ICL_LANGUAGE_CODE;
        }
        return '';
    }

    /**
     * Every language code active on the site (with WPML). Empty array when WPML is not active.
     *
     * @return string[]
     */
    private static function active_language_codes(): array
    {
        if (!defined('ICL_LANGUAGE_CODE')) {
            return [];
        }
        $langs = apply_filters('wpml_active_languages', null, 'skip_missing=0');
        return is_array($langs) ? array_keys($langs) : [];
    }

    /**
     * Fetch footer-menu items as a cache-safe stdClass array.
     *
     * Casts WP_Post to stdClass so the dynamic props from wp_setup_nav_menu_item() (url, title, target, classes)
     * survive serialize() — WP_Post::__sleep() would otherwise drop them.
     */
    private static function resolve_items(string $location, string $fallback): array
    {
        if (has_nav_menu($location)) {
            $menu = Timber::get_menu($location);
            $items = $menu ? wp_get_nav_menu_items($menu->id) : [];
        } else {
            $items = wp_get_nav_menu_items($fallback);
        }

        if (!is_array($items)) {
            return [];
        }

        return array_map(
            static fn($item) => is_object($item) ? (object) get_object_vars($item) : $item,
            $items
        );
    }
}
