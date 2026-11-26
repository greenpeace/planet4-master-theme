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
        add_action('wp_head', [$this, 'set_structured_data_json_schema'], 10);
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
     * Prints the site's structured data (JSON-LD) in the document head.
     *
     */
    public function set_structured_data_json_schema(): void
    {
        $site_url = home_url();
        $site_name = html_entity_decode(wp_strip_all_tags(get_bloginfo('name')), ENT_QUOTES, 'UTF-8');
        $nav_footer_social_items = wp_get_nav_menu_items('footer-social') ?: null;
        $social_links = $nav_footer_social_items ?
            array_values(array_filter(wp_list_pluck($nav_footer_social_items, 'url'))) : null;

        $website = [
            '@type' => 'WebSite',
            'url' => $site_url,
            'potentialAction' => [
                '@type' => 'SearchAction',
                'target' => $site_url . '/?s={search_term_string}&orderby=_score',
                'query-input' => 'required name=search_term_string',
            ],
        ];

        $organization = [
            '@type' => 'NGO',
            'name' => $site_name,
            'description' => html_entity_decode(wp_strip_all_tags(get_bloginfo('description')), ENT_QUOTES, 'UTF-8'),
            'url' => $site_url,
            'logo' => 'https://www.greenpeace.org/global/static/img/favicon-500x500.png',
        ];

        if ($social_links) {
            $organization['sameAs'] = $social_links;
        }

        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [ $website, $organization ],
        ];

        if (is_single() && 'post' === get_post_type()) {
            $post_id = get_queried_object_id();
            $author_id = (int) get_post_field('post_author', $post_id);
            $author = get_the_author_meta('display_name', $author_id);
            $author_override = get_post_meta($post_id, 'p4_author_override', true);

            $article = [
                '@type' => 'Article',
                'headline' => html_entity_decode(wp_strip_all_tags(get_the_title($post_id)), ENT_QUOTES, 'UTF-8'),
                'description' => html_entity_decode(wp_strip_all_tags(get_the_excerpt($post_id)), ENT_QUOTES, 'UTF-8'),
                'datePublished' => get_the_date('c', $post_id),
                'dateModified' => get_the_modified_date('c', $post_id),
                'author' => [
                    '@type' => 'Person',
                    'name' => empty($author_override) ? $author : $author_override,
                ],
            ];

            $image = get_the_post_thumbnail_url($post_id, 'full');
            if ($image) {
                $article['image'] = $image;
            }

            if (empty($author_override)) {
                $article['author']['url'] = get_author_posts_url($author_id);
            }

            $schema['@graph'][] = $article;
        }

        $flags = JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_HEX_TAG | JSON_PRETTY_PRINT;

        printf(
            '<script type="application/ld+json">%s</script>' . "\n",
            wp_json_encode($schema, $flags)
        );
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
