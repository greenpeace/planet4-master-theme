<?php

declare(strict_types=1);

namespace P4\MasterTheme;

/**
 * Class HeadManager
 *
 */
class HeadManager
{
    /**
     * Constructor
     *
     */
    public function __construct()
    {
        add_action('wp_head', [$this, 'add_noindex_metatag'], 10);
        add_action('wp_head', [$this, 'enable_vwo_anti_flicker'], 10);
        add_action('admin_head', [$this, 'filter_p4_settings_menu']);
        add_action('admin_head', [$this, 'add_help_sidebar']);

        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('wp_head', 'wp_generator');

        if (!has_action('wp_print_styles', 'print_emoji_styles')) {
            return;
        }
        remove_action('wp_print_styles', 'print_emoji_styles');
    }

    /**
     * Adds the VWO Anti-Flicker script in the document head.
     *
     * The script is added to the `<head>` section to ensure it executes
     * before page rendering, minimizing layout shifts or style flashes.
     *
     */
    public function enable_vwo_anti_flicker(): void
    {
        {
            $enable_vwo = planet4_get_option('vwo_account_id') ?? null;

        if (!$enable_vwo) {
            return;
        }

            echo '
                <script>
                    window.vwo_$ = window.vwo_$ || function() {
                        (window._vwoQueue = window._vwoQueue || []).push(arguments);
                        return {
                        vwoCss: function() {}
                        };
                    };
                </script>

                <script>
                    vwo_$("body").vwoCss({"visibility":"visible !important"});
                </script>
            ';
        }
    }

    /**
     * Adds a "noindex" meta tag to the head of singular posts that are excluded from search.
     *
     */
    public function add_noindex_metatag(): void
    {
        if (!is_singular()) {
            return;
        }

        global $post;

        $exclude_from_search = get_post_meta($post->ID, 'ep_exclude_from_search', true);

        if (!$exclude_from_search) {
            return;
        }

        echo '<meta name="robots" content="noindex">' . PHP_EOL;
    }

    /**
     * Sorts the Planet 4 settings submenu items alphabetically by their titles.
     *
     */
    public function filter_p4_settings_menu(): void
    {
        global $submenu;

        if (!isset($submenu['planet4_settings_navigation'])) {
            return;
        }

        uasort($submenu['planet4_settings_navigation'], fn ($a, $b) => $a[0] <=> $b[0]);
    }

    /**
     * Adds a link to the Planet 4 Handbook in the WordPress admin help sidebar.
     *
     */
    public function add_help_sidebar(): void
    {
        if (!get_current_screen()) {
            return;
        }

        $screen = get_current_screen();
        $sidebar = $screen->get_help_sidebar();

        $sidebar .= '<p><a target="_blank" href="https://planet4.greenpeace.org/">Planet 4 Handbook</a></p>';

        $screen->set_help_sidebar($sidebar);
    }
}
