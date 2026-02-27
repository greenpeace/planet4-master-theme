<?php

namespace P4\MasterTheme\Api;

use WP_Query;
use WP_REST_Request;
use WP_REST_Server;
use P4\MasterTheme\Search\Search as SearchClass;
use P4\MasterTheme\Search\SearchPage;
use P4\MasterTheme\Search\Filters\ContentTypes;

class Search
{
    /**
     * @example GET /wp-json/planet4/v1/search/?s=foo
     */
    public static function register_endpoint(): void
    {
        /**
         * Endpoint to get partial search results
         * Returns JSON with HTML + metadata
         */
        register_rest_route(
            'planet4/v1',
            'search',
            [
                [
                    'permission_callback' => static fn() => true,
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function (WP_REST_Request $request) {
                        $params = $request->get_params();

                        $order_map = [
                            '_score' => ['orderby' => 'relevance', 'order' => 'DESC'],
                            'post_date' => ['orderby' => 'date', 'order' => 'DESC'],
                            'post_date_asc' => ['orderby' => 'date', 'order' => 'ASC'],
                        ];

                        if (isset($params['orderby'], $order_map[$params['orderby']])) {
                            $params = array_merge($params, $order_map[$params['orderby']]);
                        }

                        $query = new WP_Query();
                        $query->set('ep_integrate', true);
                        $query->query($params);

                        SearchClass::validate_filters($query);

                        $page = new SearchPage($query);

                        $posts = array_map(static function ($post) {

                            setup_postdata($post);

                            $thumbnail_id = get_post_thumbnail_id($post);
                            $thumbnail_src = $thumbnail_id
                                ? wp_get_attachment_image_src($thumbnail_id, 'medium')
                                : null;

                            return [
                                'id' => $post->ID,
                                'title' => get_the_title($post),
                                'link' => get_permalink($post),
                                'post_type' => get_post_type($post),
                                'excerpt' => get_the_excerpt($post),
                                'date' => get_the_date('c', $post),
                                'featured_image' => $thumbnail_src ? [
                                    'url' => $thumbnail_src[0],
                                    'width' => $thumbnail_src[1],
                                    'height' => $thumbnail_src[2],
                                    'alt' => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
                                ] : null,
                            ];
                        }, $query->posts);

                        wp_reset_postdata();

                        return [
                            'posts' => $posts,
                            'current_page' => $page->context['current_page'] ?? 1,
                            'found_posts' => $page->context['found_posts'] ?? 0,
                            'posts_per_load' =>
                                $page->context['load_more']['posts_per_load'] ?? SearchPage::POSTS_PER_LOAD,
                        ];
                    },
                ],
            ],
        );
        register_rest_route(
            'planet4/v1',
            'search-taxonomies',
            [
                [
                    'permission_callback' => static fn() => true,
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function (WP_REST_Request $request) {

                        $args = $request->get_params();

                        // Force full query with IDs only
                        $args['posts_per_page'] = -1;
                        $args['paged'] = 1;
                        $args['fields'] = 'ids';

                        $query = new WP_Query();
                        $query->set('ep_integrate', true);
                        $query->query($args);

                        SearchClass::validate_filters($query);

                        $aggregate_terms = static function (array $post_ids, string $taxonomy): array {
                            $terms = [];

                            foreach ($post_ids as $post_id) {
                                $post_terms = get_the_terms($post_id, $taxonomy);
                                if (!$post_terms) {
                                    continue;
                                }

                                foreach ($post_terms as $term) {
                                    if (!isset($terms[$term->term_id])) {
                                        $terms[$term->term_id] = [
                                            'id' => $term->term_id,
                                            'slug' => $term->slug,
                                            'name' => $term->name,
                                            'count' => 0,
                                        ];
                                    }
                                    $terms[$term->term_id]['count']++;
                                }
                            }

                            return array_values($terms);
                        };

                        // Map post type slugs to numeric IDs
                        $ct_to_id = ContentTypes::get_ids_map();

                        $post_types = [];
                        foreach ($query->posts as $post_id) {
                            $slug = get_post_type($post_id);
                            $id = $ct_to_id[$slug] ?? 0;

                            if (!isset($post_types[$id])) {
                                $post_types[$id] = [
                                    'id' => $id,
                                    'slug' => $slug,
                                    'count' => 0,
                                ];
                            }

                            $post_types[$id]['count']++;
                        }
                        $post_types = array_values($post_types);

                        return [
                            'post_types' => $post_types,
                            'categories' => $aggregate_terms($query->posts, 'category'),
                            'p4_page_type' => $aggregate_terms($query->posts, 'p4-page-type'),
                            'action_type' => $aggregate_terms($query->posts, 'action-type'),
                        ];
                    },
                ],
            ]
        );
    }
}
