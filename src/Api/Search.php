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

                        ob_start();
                        $page->render_partial();
                        $html = ob_get_clean();

                        $posts = array_map(static function ($post) {

                            $serialize_terms = static function (int $post_id, string $taxonomy): array {
                                return array_map(
                                    static fn($term) => [
                                        'id'   => $term->term_id,
                                        'name' => $term->name,
                                        'slug' => $term->slug,
                                    ],
                                    get_the_terms($post_id, $taxonomy) ?: []
                                );
                            };

                            return [
                                'id'        => $post->ID,
                                'title'     => get_the_title($post),
                                'link'      => get_permalink($post),
                                'post_type' => get_post_type($post),

                                'categories'    => $serialize_terms($post->ID, 'category'),
                                'tags'          => $serialize_terms($post->ID, 'post_tag'),
                                'p4_page_type'  => $serialize_terms($post->ID, 'p4-page-type'),
                            ];

                        }, $query->posts);

                        return [
                            'html' => $html,
                            'posts' => $posts,
                            'current_page' => $page->context['current_page'] ?? 1,
                            'found_posts' => $page->context['found_posts'] ?? 0,
                            'posts_per_load' => $page->context['load_more']['posts_per_load'] ?? SearchPage::POSTS_PER_LOAD,
                        ];
                    }
                ],
            ],
        );
    }
}
