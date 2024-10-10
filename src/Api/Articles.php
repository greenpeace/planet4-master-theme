<?php

namespace P4\MasterTheme\Api;

use P4\MasterTheme\Blocks\Articles as ArticlesBlock;
use WP_REST_Server;

/**
 * Articles block API
 */
class Articles
{
    /**
     * Register endpoint to retrieve the articles for the Articles block.
     *
     * @example GET /wp-json/planet4/v1/get-posts/
     */
    public static function register_endpoint(): void
    {
        register_rest_route(
            'planet4/v1',
            'get-posts',
            [
                [
                    'permission_callback' => static function () {
                        return true;
                    },
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function ($request) {
                        $covers = ArticlesBlock::get_posts($request->get_params());
                        return rest_ensure_response($covers);
                    },
                ],
            ]
        );
    }
}
