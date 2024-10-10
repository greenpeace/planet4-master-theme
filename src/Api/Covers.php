<?php

namespace P4\MasterTheme\Api;

use P4\MasterTheme\Blocks\Covers as CoversBlock;
use WP_REST_Server;

/**
 * Covers block API
 */
class Covers
{
    /**
     * Register endpoint to retrieve the covers for the Covers block.
     *
     * @example GET /wp-json/planet4/v1/get-covers/
     */
    public static function register_endpoint(): void
    {
        register_rest_route(
            'planet4/v1',
            'get-covers',
            [
                [
                    'permission_callback' => static function () {
                        return true;
                    },
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function ($request) {
                        $covers = CoversBlock::get_covers($request->get_params());
                        return rest_ensure_response($covers);
                    },
                ],
            ]
        );
    }
}
