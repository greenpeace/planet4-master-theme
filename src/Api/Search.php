<?php

namespace P4\MasterTheme\Api;

use WP_Query;
use WP_REST_Request;
use WP_REST_Server;
use P4\MasterTheme\Search\Search as SearchClass;
use P4\MasterTheme\Search\SearchPage;

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
                        $query = new WP_Query();
                        $query->set('ep_integrate', true);
                        $query->query($request->get_params());

                        SearchClass::validate_filters($query);

                        $page = new SearchPage($query);

                        $posts = array_map(static function ($post) {

                            setup_postdata($post);

                            $thumbnail_id = get_post_thumbnail_id($post);
                            $thumbnail_src = $thumbnail_id
                                ? wp_get_attachment_image_src($thumbnail_id, 'medium')
                                : null;

                            return [
                                'id'        => $post->ID,
                                'title'     => get_the_title($post),
                                'link'      => get_permalink($post),
                                'post_type' => get_post_type($post),
                                'excerpt'   => get_the_excerpt($post),
                                'date' => get_the_date('c', $post),
                                'featured_image' => $thumbnail_src ? [
                                    'url'    => $thumbnail_src[0],
                                    'width'  => $thumbnail_src[1],
                                    'height' => $thumbnail_src[2],
                                    'alt'    => get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
                                ] : null,
                            ];

                        }, $query->posts);

                        wp_reset_postdata();

                        return [
                            'posts' => $posts,
                            'current_page' => $page->context['current_page'] ?? 1,
                            'found_posts' => $page->context['found_posts'] ?? 0,
                            'posts_per_load' => $page->context['load_more']['posts_per_load'] ?? SearchPage::POSTS_PER_LOAD,
                        ];
                    }
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

                        $args['posts_per_page'] = -1;
                        $args['paged'] = 1;
                        $args['fields'] = 'ids';

                        $query = new WP_Query();
                        $query->set('ep_integrate', true);
                        $query->query($args);

                        SearchClass::validate_filters($query);

                        $aggregate = static function (array $post_ids, string $taxonomy): array {

                            $terms = [];

                            foreach ($post_ids as $post_id) {
                                $post_terms = get_the_terms($post_id, $taxonomy);
                                if (!$post_terms) {
                                    continue;
                                }

                                foreach ($post_terms as $term) {
                                    if (!isset($terms[$term->term_id])) {
                                        $terms[$term->term_id] = [
                                            'id'    => $term->term_id,
                                            'slug'  => $term->slug,
                                            'name'  => $term->name,
                                            'count' => 0,
                                        ];
                                    }
                                    $terms[$term->term_id]['count']++;
                                }
                            }

                            return array_values($terms);
                        };

                        // Post types
                        $post_types = [];
                        foreach ($query->posts as $post_id) {
                            $type = get_post_type($post_id);
                            $post_types[$type] = ($post_types[$type] ?? 0) + 1;
                        }

                        return [
                            'post_types'   => array_map(
                                fn($slug, $count) => ['slug' => $slug, 'count' => $count],
                                array_keys($post_types),
                                $post_types
                            ),
                            'categories'   => $aggregate($query->posts, 'category'),
                            'p4_page_type' => $aggregate($query->posts, 'p4-page-type'),
                            'action_type' => $aggregate($query->posts, 'action-type'),
                        ];
                    }
                ],
            ]
        );
    }
}
