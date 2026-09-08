<?php

namespace P4\MasterTheme\Admin;

use WP_REST_Request;

class RestCacheBaseline
{
    private const KEY = 'p4_rest_baseline_posts';

    public function load(): void
    {
        add_filter('rest_pre_dispatch', [$this, 'get'], 10, 3);
        add_filter('rest_post_dispatch', [$this, 'set'], 10, 3);
    }

    public function get($result, $server, WP_REST_Request $request)
    {
        if ('/wp/v2/posts' !== $request->get_route() || 'GET' !== $request->get_method()) {
            return $result;
        }

        $cached = get_transient(self::KEY);

        if (false !== $cached && isset($cached['data'], $cached['headers'])) {
            error_log('BASELINE: HIT');
            $response = rest_ensure_response($cached['data']);
            $response->header('X-P4-Cache', 'HIT');

            foreach ($cached['headers'] as $name => $value) {
                $response->header($name, $value);
            }

            return $response;
        }

        error_log('BASELINE: MISS');
        return $result;
    }

    public function set($response, $server, WP_REST_Request $request)
    {
        if ('/wp/v2/posts' !== $request->get_route() || 'GET' !== $request->get_method()) {
            return $response;
        }

        // Already served from cache in get() — don't overwrite the header or re-store.
        $headers = $response->get_headers();
        if (isset($headers['X-P4-Cache']) && 'HIT' === $headers['X-P4-Cache']) {
            return $response;
        }

        if (!$response->is_error()) {
            set_transient(self::KEY, [
                'data' => $response->get_data(),
                'headers' => [
                    'X-WP-Total' => $response->get_headers()['X-WP-Total'] ?? null,
                    'X-WP-TotalPages' => $response->get_headers()['X-WP-TotalPages'] ?? null,
                ],
            ], 5 * MINUTE_IN_SECONDS);
            error_log('BASELINE: stored');
        }

        $response->header('X-P4-Cache', 'MISS');

        return $response;
    }
}
