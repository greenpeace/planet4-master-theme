<?php

/**
 * @package P4\MasterTheme\Admin
 */

namespace P4\MasterTheme\Admin;

use WP_REST_Request;
use WP_REST_Response;

class RestCache
{
    private const CACHE_GROUP = 'p4_rest';

    private const CACHEABLE_ROUTES = [
        '/wp/v2/posts',
        '/wp/v2/p4_action',
        '/wp/v2/p4-page-type',
        '/wp/v2/categories',
        '/wp/v2/tags',
    ];

    public function load(): void
    {
        add_filter('rest_pre_dispatch', [$this, 'get'], 10, 3);
        add_filter('rest_post_dispatch', [$this, 'set'], 10, 3);

        error_log(
            'Persistent object cache: ' .
            (wp_using_ext_object_cache() ? 'YES' : 'NO')
        );
    }

    public function get($result, $server, WP_REST_Request $request)
    {
        if (!$this->is_cacheable($request)) {
            return $result;
        }

        $key = $this->get_cache_key($request);

        $cached = wp_cache_get($key, self::CACHE_GROUP);

        if (false !== $cached) {
            // Don't mutate the object stored in the cache.
            if ($cached instanceof WP_REST_Response) {
                $response = clone $cached;
                $response->header('X-P4-Cache', 'HIT');

                return $response;
            }

            return $cached;
        }

        return $result;
    }

    public function set($response, $server, WP_REST_Request $request)
    {
        if (!$this->is_cacheable($request)) {
            return $response;
        }

        if ($response->is_error()) {
            return $response;
        }

        wp_cache_set(
            $this->get_cache_key($request),
            $response,
            self::CACHE_GROUP,
            HOUR_IN_SECONDS
        );

        // This response wasn't served from cache.
        $response->header('X-P4-Cache', 'MISS');

        return $response;
    }

    private function is_cacheable(WP_REST_Request $request): bool
    {
        if ('GET' !== $request->get_method()) {
            return false;
        }

        return in_array(
            $request->get_route(),
            self::CACHEABLE_ROUTES,
            true
        );
    }

    private function get_cache_key(WP_REST_Request $request): string
    {
        return md5(wp_json_encode([
            'route' => $request->get_route(),
            'params' => $request->get_params(),
        ]));
    }
}
?>
