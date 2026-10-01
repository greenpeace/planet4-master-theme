<?php

namespace P4\MasterTheme\Api;

/**
 * Register REST endpoints for the dynamic listing pages:
 * One for the posts and another one to get all the filters with only id and name.
 *
 * @example GET /wp-json/planet4/v1/listing-posts
 */
class ListingPageEndpoint
{
    private const REST_NAMESPACE = 'planet4/v1';
    private const CACHE_GROUP = 'p4_listing_posts';
    private const CACHE_TTL = 300;
    private const CACHE_VERSION = 'v1';
    private const TERM_TAXONOMIES = ['category', 'post_tag', 'p4-page-type'];
    private const CARD_IMAGE_MIN_WIDTH = 400;

    private const TAXONOMY_ARGS = [
        'tags' => 'post_tag',
        'categories' => 'category',
        'p4-page-type' => 'p4-page-type',
        'action-type' => 'action-type',
    ];

    public static function register_endpoint(): void
    {
        $int_arg = ['type' => 'integer', 'minimum' => 1];

        register_rest_route(
            self::REST_NAMESPACE,
            'listing-posts',
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [self::class, 'handle'],
                'permission_callback' => '__return_true',
                'args' => [
                    'post_type' => ['type' => 'string', 'enum' => ['post', 'p4_action'], 'default' => 'post'],
                    'per_page' => ['type' => 'integer', 'minimum' => 1, 'maximum' => 50, 'default' => 12],
                    'page' => ['type' => 'integer', 'minimum' => 1, 'default' => 1],
                    'author' => $int_arg,
                    'tags' => $int_arg,
                    'categories' => $int_arg,
                    'p4-page-type' => $int_arg,
                    'action-type' => $int_arg,
                ],
            ]
        );

        register_rest_route(
            self::REST_NAMESPACE,
            '/listing-filters',
            [
                'methods' => 'GET',
                'permission_callback' => '__return_true',
                'callback' => function () {
                    $to_options = fn($terms) => array_map(
                        fn($term) => [ 'id' => $term->term_id, 'name' => $term->name ],
                        $terms
                    );

                    return [
                        'post_types' =>
                            $to_options(get_terms([ 'taxonomy' => 'p4-page-type', 'hide_empty' => true ])),
                        'categories' =>
                            $to_options(get_terms([ 'taxonomy' => 'category', 'hide_empty' => true ])),
                        'tags' =>
                            $to_options(get_terms([ 'taxonomy' => 'post_tag', 'hide_empty' => true ])),
                    ];
                },
            ]
        );
    }

    public static function handle(\WP_REST_Request $request): \WP_REST_Response
    {
        $known = ['post_type', 'per_page', 'page', 'author', 'tags', 'categories', 'p4-page-type', 'action-type'];
        $args = array_intersect_key($request->get_params(), array_flip($known));
        ksort($args);

        $cache_key = implode(':', [
            self::CACHE_VERSION,
            md5(wp_json_encode($args)),
            wp_cache_get_last_changed('posts'),
            wp_cache_get_last_changed('terms'),
        ]);

        $result = wp_cache_get($cache_key, self::CACHE_GROUP);
        if ($result === false) {
            $result = self::query($args);
            wp_cache_set($cache_key, $result, self::CACHE_GROUP, self::CACHE_TTL);
        }

        $response = new \WP_REST_Response($result['items']);
        $response->header('X-WP-Total', (string) $result['total']);
        $response->header('X-WP-TotalPages', (string) $result['totalPages']);
        $response->header('Cache-Control', 'public, max-age=' . self::CACHE_TTL);

        return $response;
    }

    public static function query(array $args): array
    {
        $query_args = [
            'post_type' => $args['post_type'] ?? 'post',
            'post_status' => 'publish',
            'posts_per_page' => $args['per_page'] ?? 12,
            'paged' => $args['page'] ?? 1,
            'ignore_sticky_posts' => true,
        ];

        if (!empty($args['author'])) {
            $query_args['author'] = (int) $args['author'];
        }

        $tax_query = [];
        foreach (self::TAXONOMY_ARGS as $arg => $taxonomy) {
            if (empty($args[$arg])) {
                continue;
            }
            $tax_query[] = [
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => [(int) $args[$arg]],
            ];
        }
        if ($tax_query) {
            $query_args['tax_query'] = array_merge(['relation' => 'AND'], $tax_query);
        }

        $query = new \WP_Query($query_args);

        update_post_thumbnail_cache($query);
        cache_users(array_unique(wp_list_pluck($query->posts, 'post_author')));

        return [
            'items' => array_map([self::class, 'format_post'], $query->posts),
            'total' => (int) $query->found_posts,
            'totalPages' => (int) $query->max_num_pages,
        ];
    }

    private static function format_post(\WP_Post $post): array
    {
        return [
            'id' => $post->ID,
            'link' => get_permalink($post),
            'date' => mysql_to_rfc3339($post->post_date),
            'title' => ['rendered' => get_the_title($post)],
            'image' => self::format_image($post),
            'terms' => self::format_terms($post),
            'excerpt' => ['rendered' => apply_filters('the_excerpt', get_the_excerpt($post))],
            'meta' => [
                'p4_author_override' => (string) get_post_meta($post->ID, 'p4_author_override', true),
            ],
            'author' => [
                'id' => (int) $post->post_author,
                'name' => get_the_author_meta('display_name', $post->post_author),
                'link' => get_author_posts_url($post->post_author),
            ],
        ];
    }

    private static function format_image(\WP_Post $post): ?array
    {
        $thumbnail_id = get_post_thumbnail_id($post);
        if (!$thumbnail_id) {
            return null;
        }

        $source_url = wp_get_attachment_url($thumbnail_id);
        if (!$source_url) {
            return null;
        }

        $metadata = wp_get_attachment_metadata($thumbnail_id) ?: [];
        $sizes = [];
        foreach (array_keys($metadata['sizes'] ?? []) as $size_name) {
            $image = wp_get_attachment_image_src($thumbnail_id, $size_name);
            if ($image) {
                $sizes[$image[0]] = (int) $image[1];
            }
        }
        if (!empty($metadata['width'])) {
            $sizes[$source_url] = (int) $metadata['width'];
        }
        asort($sizes);

        $src = $source_url;
        foreach ($sizes as $url => $width) {
            if ($width >= self::CARD_IMAGE_MIN_WIDTH) {
                $src = $url;
                break;
            }
        }

        $srcset = [];
        foreach ($sizes as $url => $width) {
            $srcset[] = "{$url} {$width}w";
        }

        return [
            'src' => $src,
            'srcset' => implode(', ', $srcset),
            'width' => $metadata['width'] ?? null,
            'height' => $metadata['height'] ?? null,
            'alt' => (string) get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true),
        ];
    }

    private static function format_terms(\WP_Post $post): array
    {
        $terms = [];

        foreach (self::TERM_TAXONOMIES as $taxonomy) {
            $list = get_the_terms($post, $taxonomy);
            if (!is_array($list)) {
                continue;
            }

            $terms[$taxonomy] = array_map(static function ($term): array {
                $link = get_term_link($term);

                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'slug' => $term->slug,
                    'link' => is_wp_error($link) ? '' : $link,
                ];
            }, $list);
        }

        return $terms;
    }
}
