<?php

declare(strict_types=1);

namespace P4\MasterTheme\Api;

use WP_REST_Server;
use P4\MasterTheme\Settings as Planet4Settings;
use P4\MasterTheme\Settings\Features;

/**
 * Instance settings API
 */
class Settings
{
    /**
     * Register endpoint to read settings.
     *
     * @example GET /wp-json/planet4/v1/settings/
     */
    public static function register_endpoint(): void
    {
        register_rest_route(
            'planet4/v1',
            'settings',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => function () {
                    return [
                        'planet4_options' => self::list_planet4_settings(),
                        'planet4_features' => self::list_planet4_features(),
                        'plugins' => self::list_plugins(),
                        'themes' => self::list_themes(),
                    ];
                },
                'permission_callback' => function ($request) {
                    if (current_user_can('manage_options')) {
                        return true;
                    }

                    if (!defined('PLANET4_API_KEY') || empty(PLANET4_API_KEY)) {
                        return false;
                    }

                    $token = $request->get_header('X-Auth-Token');
                    return !empty($token) && $token === PLANET4_API_KEY;
                },
            ]
        );
    }

    /**
     * List of planet4 settings (key/value), sorted by language.
     *
     * @return array List of settings, by language.
     */
    private static function list_planet4_settings(): array
    {
        $site_locale = get_locale();
        $is_multilingual = function_exists('icl_get_languages');

        $settings = [];
        $languages = $is_multilingual
            ? array_column(icl_get_languages(), 'code')
            : [ $site_locale ];

        foreach ($languages as $lang) {
            do_action('wpml_switch_language', $lang);
            $local_options = get_option(Planet4Settings::KEY);
            $settings[$lang] = $local_options;

            foreach (Features::external_settings() as $setting) {
                $settings[$lang][$setting::KEY] = $setting::get_option();
            }
            ksort($settings[$lang]);
        }

        do_action('wpml_switch_language', $site_locale);
        ksort($settings);


        return $settings;
    }

    /**
     * List of planet4 features (key/bool).
     *
     * @return array List of features.
     */
    private static function list_planet4_features(): array
    {
        $result = [];
        $features = Planet4Settings\Features::all_features();

        foreach ($features as $feature) {
            $result[$feature::id()] = $feature::is_active($feature::id());
        }

        return $result;
    }

    /**
     * List of plugins installed and their active state.
     *
     * @return array List of plugins installed.
     */
    private static function list_plugins(): array
    {
        $plugins = get_plugins();
        $list = [];
        foreach ($plugins as $key => &$plugin) {
            $list[ $key ] = [
                'name' => $plugin['Name'],
                'version' => $plugin['Version'],
                'active' => is_plugin_active($key),
            ];
        }
        ksort($list);
        return $list;
    }

    /**
     * List of themes installed.
     *
     * @return array List of themes installed.
     */
    private static function list_themes(): array
    {
        $packages = get_option('greenpeace_packages');
        $current = get_stylesheet_directory();
        $themes = wp_get_themes();
        $list = [];
        foreach ($themes as $name => $theme) {
            $package_name = 'greenpeace/' . basename($theme->get_stylesheet_directory());
            $package = array_filter($packages, fn($p) => $p[0] === $package_name)[0] ?? null;
            $list[ $name ] = [
                'name' => $theme->name,
                'version' => $package ? $package[1] : $theme->version,
                'dir' => $theme->get_stylesheet_directory(),
                'parent' => $theme->parent_theme,
                'active' => $theme->get_stylesheet_directory() === $current,
            ];
        }
        ksort($list);
        return $list;
    }
}
