<?php

declare(strict_types=1);

namespace P4\MasterTheme\Api;

use P4\MasterTheme\Blocks\Gallery as GalleryBlock;
use WP_REST_Server;

/**
 * Gallery block API
 */
class Gallery
{
    /**
     * Register endpoint to read settings.
     *
     * @example GET /wp-json/planet4/v1/gallery/images/
     */
    public static function register_endpoint(): void
    {
        /**
         * Endpoint to retrieve the images for the Gallery block
         */
        register_rest_route(
            'planet4/v1',
            'gallery/images',
            [
                [
                    'permission_callback' => static function () {
                        return true;
                    },
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function ($request) {
                        $images = GalleryBlock::get_images($request->get_params());
                        return rest_ensure_response($images);
                    },
                ],
            ]
        );
    }
}
