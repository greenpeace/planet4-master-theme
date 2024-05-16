<?php

namespace P4\MasterTheme\Api;

use WP_Query;
use WP_REST_Request;
use WP_REST_Server;
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
         * Rendered in HTML
         */
        register_rest_route(
            'planet4/v1',
            'search',
            [
                [
                    'permission_callback' => static fn() => true,
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function (WP_REST_Request $request): void {
                        $query = new WP_Query();
                        $query->set('ep_integrate', true);
                        $query->query($request->get_params());

                        $page = new SearchPage($query);
                        $page->render_partial();
                    },
                ],
            ]
        );
    }
}
