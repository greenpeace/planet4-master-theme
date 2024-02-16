<?php

namespace P4\MasterTheme\Api;

use P4\MasterTheme\ListingPage as ListingPageClass;
use WP_REST_Server;

/**
 * Listing Page API
 */
class ListingPage
{
    /**
     * Register endpoint to read settings.
     *
     * @example GET /wp-json/planet4/v1/listing-page/posts
     */
    public static function register_endpoint(): void
    {
        /**
         * Endpoint to retrieve the posts for dynamically rendered listing pages.
         */
        register_rest_route(
            'planet4/v1',
            'listing-page/posts',
            [
                [
                    'permission_callback' => static function () {
                        return true;
                    },
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function () {
                        $posts = ListingPageClass::get_posts();
                        return rest_ensure_response($posts);
                    },
                ],
            ]
        );
    }
}
