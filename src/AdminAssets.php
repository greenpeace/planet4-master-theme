<?php

declare(strict_types=1);

namespace P4\MasterTheme;

/**
 * Add assets to admin pages
 */
class AdminAssets
{
    /**
     * Enqueue js scripts
     */
    public static function enqueue_js(): void
    {
        if (get_current_screen()->id !== 'nav-menus') {
            return;
        }

        self::enqueue_menu_editor_script();
    }

    /**
     * Enqueue menu editor script, define menu editor configuration.
     */
    private static function enqueue_menu_editor_script(): void
    {
        $theme_dir = get_template_directory_uri();
        $menus = get_nav_menu_locations();
        $navbar_location = 'navigation-bar-menu';
        $donate_location = 'donate-menu';

        if (
            ! isset($menus[ $navbar_location ])
            && ! isset($menus[ $donate_location ])
        ) {
            return;
        }

        // Configuration per menu location.
        $default_conf = [
            $navbar_location => [
                'maxDepth' => 1,
                'depthConf' => [
                    0 => [
                        'maxItems' => 5,
                        'maxChars' => 18,
                    ],
                    1 => [
                        'maxItems' => 10,
                        'maxChars' => 32,
                    ],
                ],
            ],
            $donate_location => [
                'maxDepth' => 1,
                'depthConf' => [
                    0 => [
                        'maxItems' => 1,
                        'maxChars' => 18,
                    ],
                    1 => [
                        'maxItems' => 10,
                        'maxChars' => 32,
                    ],
                ],
            ],
        ];

        $conf = apply_filters('planet4_menu_config', $default_conf);

        wp_enqueue_script(
            'menu-editor',
            $theme_dir . '/assets/build/menu_editor.js',
            [ 'nav-menu' ],
            1,
            true
        );

        wp_add_inline_script(
            'menu-editor',
            sprintf(
                'const p4_menu_config = %s;',
                wp_json_encode($conf, \JSON_FORCE_OBJECT)
            ),
            'before'
        );

        // Sets translated strings for a JS script.
        wp_set_script_translations('menu-editor', 'planet4-master-theme-backend', get_template_directory() . '/languages');
    }
}
