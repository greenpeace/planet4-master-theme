<?php

namespace P4\MasterTheme\Api;

use WP_REST_Request;
use WP_REST_Server;

class PostsList
{
    public static function register_endpoint(): void
    {
        register_rest_route(
            'planet4/v1',
            'posts-list',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function (WP_REST_Request $request) {
                        $endpoint = (string) $request->get_param('endpoint');
                        $params = $request->get_query_params();

                        // Remove endpoint from params as it's not part of the query args
                        unset($params['endpoint'], $params['rest_route']);

                        $response = self::get_posts($endpoint, $params);

                        return rest_ensure_response($response);
                    },
                    'permission_callback' => static function () {
                        return true;
                    },
                ],
            ]
        );
    }

    /**
     * Fetch posts with app-level cache.
     *
     * @param string $endpoint The REST endpoint ('posts' or custom post type slug).
     * @param array  $args Query args.
     *
     * @return array{data: array, totalPages: int, total: int}
     */
    public static function get_posts(string $endpoint, array $args = []): array
    {
        $endpoint = sanitize_key($endpoint);

        if ('' === $endpoint) {
            return [
                'data' => [],
                'totalPages' => 1,
                'total' => 0,
            ];
        }

        $cache_key = self::build_cache_key($endpoint, $args);
        $endpoint_cache_key = self::get_endpoint_cache_key($endpoint);
        $posts_cache = wp_cache_get($endpoint_cache_key);

        if (is_array($posts_cache) && array_key_exists($cache_key, $posts_cache)) {
            return $posts_cache[$cache_key];
        }

        // Build the REST URL and fetch from the WordPress REST API
        $rest_url = rest_url(sprintf('wp/v2/%s', $endpoint));
        $query_string = build_query(self::sanitize_args($args));
        $full_url = $rest_url . '?' . $query_string;

        $response = wp_remote_get($full_url, [
            'timeout' => 10,
        ]);

        if (is_wp_error($response)) {
            return [
                'data' => [],
                'totalPages' => 1,
                'total' => 0,
            ];
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (! is_array($data)) {
            return [
                'data' => [],
                'totalPages' => 1,
                'total' => 0,
            ];
        }

        $headers = wp_remote_retrieve_headers($response);
        $total_pages = absint($headers['x-wp-totalpages'] ?? 1);
        $total = absint($headers['x-wp-total'] ?? 0);

        $cached_response = [
            'data' => $data,
            'totalPages' => $total_pages,
            'total' => $total,
        ];

        if (! is_array($posts_cache)) {
            $posts_cache = [];
        }

        $posts_cache[$cache_key] = $cached_response;
        wp_cache_set($endpoint_cache_key, $posts_cache, null, DAY_IN_SECONDS);

        return $cached_response;
    }

    /**
     * Sanitize query args before passing to REST API.
     *
     * @param array $args Raw args from request.
     *
     * @return array Sanitized args.
     */
    private static function sanitize_args(array $args): array
    {
        $sanitized = [];

        foreach ($args as $key => $value) {
            $key = sanitize_key($key);

            if ('' === $key || null === $value) {
                continue;
            }

            if (is_array($value)) {
                $sanitized[$key] = array_map('sanitize_text_field', $value);
            } else {
                $sanitized[$key] = sanitize_text_field((string) $value);
            }
        }

        return $sanitized;
    }

    /**
     * Build a cache key that is stable for the given query args.
     *
     * @param string $endpoint The endpoint.
     * @param array  $args Query args used to build the payload.
     *
     * @return string
     */
    private static function build_cache_key(string $endpoint, array $args): string
    {
        return sprintf(
            '%s:%s',
            $endpoint,
            md5((string) wp_json_encode($args))
        );
    }

    /**
     * Build the per-endpoint cache bucket key to allow invalidation for all variants of that endpoint.
     *
     * @param string $endpoint The endpoint.
     *
     * @return string
     */
    private static function get_endpoint_cache_key(string $endpoint): string
    {
        return sprintf('planet4-posts-list:%s', sanitize_key($endpoint));
    }

    /**
     * Invalidate the posts list cache for affected post types.
     *
     * @return void
     */
    public static function invalidate_posts_cache(...$args): void
    {
        $post_id = $args[0] ?? 0;

        if (! absint($post_id)) {
            return;
        }

        $post = get_post($post_id);

        if (! $post) {
            return;
        }

        $post_type = $post->post_type;
        $endpoint = 'post' === $post_type ? 'posts' : $post_type;

        wp_cache_delete(self::get_endpoint_cache_key($endpoint));
    }
}

add_action('save_post', [\P4\MasterTheme\Api\PostsList::class, 'invalidate_posts_cache'], 10, 1);
add_action('publish_post', [\P4\MasterTheme\Api\PostsList::class, 'invalidate_posts_cache'], 10, 1);
add_action('delete_post', [\P4\MasterTheme\Api\PostsList::class, 'invalidate_posts_cache'], 10, 1);
