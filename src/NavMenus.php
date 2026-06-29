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
     */
    public static function invalidate_footer_cache(): void
    {
        delete_transient(self::FOOTER_SOCIAL_KEY);
        delete_transient(self::FOOTER_PRIMARY_KEY);
        delete_transient(self::FOOTER_SECONDARY_KEY);
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
     */
    private static function cached_items(string $cache_key, string $location, string $fallback): array
    {
        $items = TimberHelper::transient(
            $cache_key,
            static fn() => self::resolve_items($location, $fallback),
            DAY_IN_SECONDS
        );

        return is_array($items) ? $items : [];
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
