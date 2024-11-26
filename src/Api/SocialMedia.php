<?php

declare(strict_types=1);

namespace P4\MasterTheme\Api;

use P4\MasterTheme\Blocks\SocialMedia as SocialMediaBlock;
use WP_REST_Server;

/**
 * SocialMedia block API
 */
class SocialMedia
{
    /**
     * Endpoint to get the code for Instagram embeds in the Social Media block.
     *
     * @example GET /wp-json/planet4/v1/get-instagram-embed
     */
    public static function register_endpoint(): void
    {
        /**
         * Endpoint to get the code for Instagram embeds in the Social Media block.
         */
        register_rest_route(
            'planet4/v1',
            '/get-instagram-embed',
            [
                [
                    'permission_callback' => static function () {
                        return true;
                    },
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function ($fields) {
                        $url = $fields['url'] ?? '';
                        $embed_code = SocialMediaBlock::get_fb_oembed_html($url, 'instagram');
                        return rest_ensure_response($embed_code);
                    },
                ],
            ]
        );
    }
}
