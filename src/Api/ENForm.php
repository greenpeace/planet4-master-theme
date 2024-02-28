<?php

namespace P4\MasterTheme\Api;

use P4\MasterTheme\Blocks\ENForm as EnformBlock;
use WP_REST_Server;

/**
 * ENForm block API
 */
class ENForm
{
    /**
     * Register endpoint to read settings.
     *
     * @example GET /wp-json/planet4/v1/enform/(?P<en_page_id>\d+)/
     */
    public static function register_endpoints(): void
    {
        /**
         * Endpoint to get data for the Enform block
         */
        register_rest_route(
            'planet4/v1',
            '/enform/(?P<en_page_id>\d+)',
            [
                [
                    'methods' => WP_REST_Server::CREATABLE,
                    'callback' => static function ($request) {
                        return EnformBlock::send_enform($request);
                    },
                    'permission_callback' => static function () {
                        return true;
                    },
                ],
            ]
        );
    }
}
