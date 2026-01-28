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
        add_action('init', [$this, 'add_robots_metatag']);
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
     * Adds the "robots" metatag to the head.
     *
     */
    public function add_robots_metatag(): void
    {
        add_filter('wp_robots', function (array $robots): array {
            global $post;

            $robots['max-snippet'] = -1;
            $robots['max-video-preview'] = -1;
            $robots['max-image-preview'] = 'large';

            // Return if search engines are discouraged from indexing the website
            // See "Search engine visibility" in wp-admin > Settings > Reading
            if (!get_option('blog_public')) {
                return $robots;
            }

            $is_exclude_from_search = $post ? get_post_meta($post->ID, 'ep_exclude_from_search', true) : false;
            $is_listing_page = is_archive() || is_search() || (int) get_option('page_for_posts') === ($post->ID ?? 0);
            $allow_all_listing_pages_indexing = (bool) planet4_get_option('allow_indexing_of_all_listing_pages');

            if (is_singular() && $is_exclude_from_search) { // Single page/post excluded from search
                $robots['noindex'] = true;
            // Listing page, except the first page, when the "Allow indexing of all listing pages" setting is off:
            } elseif (is_paged() && $is_listing_page && !$allow_all_listing_pages_indexing) {
                $robots['noindex'] = true;
                $robots['follow'] = true;
            }

            return $robots;
        });
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
