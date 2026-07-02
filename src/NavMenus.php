<?php

namespace P4\MasterTheme;

use Timber\Helper as TimberHelper;
use Timber\Timber;

/**
 * Class NavMenus
 *
 * Centralises nav-menus functionality for the theme:
 *  - registers the 5 menu locations (top nav, donate, 3x footer)
 *  - serves footer-menu items from a 24h Timber transient, with invalidation on any menu mutation
 *  - applies the transparent-nav body class on the front page
 */
class NavMenus
{
    private const FOOTER_SOCIAL_KEY = 'p4_footer_social_menu';
    private const FOOTER_PRIMARY_KEY = 'p4_footer_primary_menu';
    private const FOOTER_SECONDARY_KEY = 'p4_footer_secondary_menu';

    public function __construct()
    {
        add_action('after_setup_theme', [self::class, 'register_locations'], 0);

        $invalidate = [self::class, 'invalidate_footer_cache'];
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
     * Delete all footer-menu transients. Called on any nav-menu mutation.
     *
     * On multilingual sites each language has its own cache entry, so we enumerate active language codes and delete
     * every variant. Also deletes the un-suffixed base keys for safety on sites where the plugin is inactive.
     */
    public static function invalidate_footer_cache(): void
    {
        $base_keys = [
            self::FOOTER_SOCIAL_KEY,
            self::FOOTER_PRIMARY_KEY,
            self::FOOTER_SECONDARY_KEY,
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
        $lang = self::current_language_code();
        $key = $lang !== '' ? $cache_key . '_' . $lang : $cache_key;

        $items = TimberHelper::transient(
            $key,
            static fn() => self::resolve_items($location, $fallback),
            DAY_IN_SECONDS
        );

        return is_array($items) ? $items : [];
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
     * Fetch items as a cache-safe stdClass array.
     *
     * Casts WP_Post to stdClass so the dynamic props from
     * wp_setup_nav_menu_item() (url, title, target, classes) survive
     * serialize() - WP_Post::__sleep() would otherwise drop them.
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
