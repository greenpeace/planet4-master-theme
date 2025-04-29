<?php

declare(strict_types=1);

namespace P4\MasterTheme\Api;

use WP_REST_Response;
use WP_REST_Request;

/**
 * Instance QueryLoopExtension API
 */
class QueryLoopExtension
{
    /**
     * Register endpoint to avoid 404 response when fetching multiples
     * posts within the Actions List block.
     * Please check /src/Blocks/QueryLoopExtension.php for more reference.
     *
     * @example GET /wp-json/wp/v2/p4_multipost/193
     */

    public static function register_endpoint(): void
    {
        register_rest_route(
            'wp/v2',
            '/p4_multipost/(?P<id>\d+)',
            [
                'methods' => 'GET',
                'callback' => function (WP_REST_Request $request) {
                    global $wpdb;
                    $post_type = $wpdb->get_col($wpdb->prepare(
                        "SELECT post_type FROM {$wpdb->posts} WHERE ID = %s",
                        $request->get_param('id')
                    ));
                    return new WP_REST_Response($post_type[0] ?? "", 200);
                },
                'permission_callback' => '__return_true',
            ]
        );
    }
}
