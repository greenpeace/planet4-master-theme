<?php

namespace P4\MasterTheme;

use WP_Query;
use WP_Taxonomy;

/**
 * Class P4\MasterTheme\Cloudflare\CloudflareHandler
 */
class CloudflareHandler
{
    /**
     * The constructor.
     */
    public function __construct()
    {
        add_filter('cloudflare_purge_by_url', [$this, 'aaa'], 10, 2);

        // Ensure no actions trigger a purge everything.
        simple_value_filter('cloudflare_purge_everything_actions', []);

        // Remove the menu item to the Cloudflare page.
        add_action(
            'admin_menu',
            function (): void {
                remove_submenu_page('options-general.php', 'cloudflare');
            }
        );
        // remove_submenu_page does not prevent accessing the page. Add a higher prio action that dies instead.
        add_action(
            'settings_page_cloudflare',
            function (): void {
                die('This page is blocked to prevent excessive cache purging.');
            },
            1
        );
    }

    public function aaa ($urls, $post_id) {
        // If new IA is not active return early since pagination is not being used.
        if (empty(planet4_get_option('new_ia'))) {
            return $urls;
        }
        $new_urls = [];
        // Most of this logic is copied from the start of \CF\WordPress\Hooks::getPostRelatedLinks.
        // I had to adapt it to our CS, it used snake case and old arrays.
        // I only changed the part that creates the pagination URLs.
        // And for now early return on other taxonomies as only tags need it.
        $post_type = get_post_type($post_id);

        // Purge taxonomies terms and feeds URLs.
        $post_type_taxonomies = get_object_taxonomies($post_type);

        foreach ($post_type_taxonomies as $taxonomy) {
            // Only do post tags for now, but we'll need this loop when more pages have pagination.
            if ('post_tag' !== $taxonomy) {
                continue;
            }
            // Only if taxonomy is public.
            $taxonomy_data = get_taxonomy($taxonomy);
            if ($taxonomy_data instanceof WP_Taxonomy && false === $taxonomy_data->public) {
                continue;
            }

            $terms = get_the_terms($post_id, $taxonomy);

            if (empty($terms) || is_wp_error($terms)) {
                continue;
            }

            foreach ($terms as $term) {
                $term_link = get_term_link($term);

                if (is_wp_error($term_link)) {
                    continue;
                }

                $args = [
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => - 1,
                    'tax_query' => [
                        'relation' => 'AND',
                        [
                            'taxonomy' => $taxonomy,
                            'field' => 'id',
                            'terms' => [ $term->term_id ],
                        ],
                    ],
                ];

                $query = new WP_Query($args);
                $pages = $query->post_count / get_option('posts_per_page', 10);
                if ($pages <= 1) {
                    continue;
                }

                $numbers = range(2, 1 + round($pages));

                $new_urls = array_map(fn($i) => "{$term_link}page/{$i}/", $numbers);
            }
        }

        return array_merge($urls, $new_urls);
    }
}
