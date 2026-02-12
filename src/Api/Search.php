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

                        // Prepare WP_Query
                        $query = new WP_Query();
                        $query->set('ep_integrate', true);

                        // Apply request params
                        $query->query($request->get_params());

                        // Apply your custom filters
                        SearchClass::validate_filters($query);

                        // Initialize SearchPage
                        $page = new SearchPage($query);

                        // Render posts to HTML but capture it as string
                        ob_start();
                        $page->render_partial();
                        $html = ob_get_clean();

                        // Return JSON with metadata
                        return [
                            'html' => $html,
                            'current_page' => $page->context['current_page'] ?? 1,
                            'found_posts' => $page->context['found_posts'] ?? 0,
                            'posts_per_load' => $page->context['load_more']['posts_per_load'] ?? SearchPage::POSTS_PER_LOAD,
                        ];
                    },
                ],
            ]
        );
    }
}
