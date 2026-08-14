<?php

namespace P4\MasterTheme\Api;

use WP_REST_Request;
use WP_REST_Server;

class TaxonomyList
{
    public static function register_endpoint(): void
    {
        register_rest_route(
            'planet4/v1',
            'taxonomy-list',
            [
                [
                    'methods' => WP_REST_Server::READABLE,
                    'callback' => static function (WP_REST_Request $request) {
                        $taxonomy = (string) $request->get_param('taxonomy');
                        $args = [
                            'hide_empty' => $request->get_param('hide_empty'),
                            'per_page' => $request->get_param('per_page'),
                            'orderby' => $request->get_param('orderby'),
                            'order' => $request->get_param('order'),
                        ];

                        return rest_ensure_response(self::get_taxonomy_terms($taxonomy, $args));
                    },
                    'permission_callback' => static function () {
                        return true;
                    },
                ],
            ]
        );
    }

    /**
     * Fetch taxonomy terms with a light app-level cache.
     *
     * @param string $taxonomy The taxonomy slug.
     * @param array  $args Optional query args to include in the cache key.
     *
     * @return array<int, array{id:int,name:string,slug:string}>
     */
    public static function get_taxonomy_terms(string $taxonomy, array $args = []): array
    {
        $taxonomy = sanitize_key($taxonomy);

        if ('' === $taxonomy) {
            return [];
        }

        $resolved_args = wp_parse_args(
            $args,
            [
                'hide_empty' => true,
                'per_page' => 100,
                'orderby' => 'name',
                'order' => 'ASC',
            ]
        );

        $cache_key = self::build_cache_key($taxonomy, $resolved_args);
        $taxonomy_cache_key = self::get_taxonomy_cache_key($taxonomy);
        $taxonomies = wp_cache_get($taxonomy_cache_key);

        if (is_array($taxonomies) && array_key_exists($cache_key, $taxonomies)) {
            return $taxonomies[$cache_key];
        }

        $terms = get_terms(
            [
                'taxonomy' => $taxonomy,
                'hide_empty' => filter_var($resolved_args['hide_empty'], FILTER_VALIDATE_BOOLEAN) ?? true,
                'number' => max(1, absint($resolved_args['per_page']) ?: 100),
                'orderby' => sanitize_key((string) $resolved_args['orderby']) ?: 'name',
                'order' => strtoupper((string) $resolved_args['order']) === 'DESC' ? 'DESC' : 'ASC',
            ]
        );

        if (is_wp_error($terms)) {
            return [];
        }

        $data = array_map(
            static function ($term): array {
                return [
                    'id' => (int) $term->term_id,
                    'name' => (string) $term->name,
                    'slug' => (string) $term->slug,
                ];
            },
            is_array($terms) ? $terms : []
        );

        if (!is_array($taxonomies)) {
            $taxonomies = [];
        }

        $taxonomies[$cache_key] = $data;
        wp_cache_set($taxonomy_cache_key, $taxonomies, null, DAY_IN_SECONDS);

        return $data;
    }

    /**
     * Build a taxonomy cache key that is stable for the given args.
     *
     * @param string $taxonomy The taxonomy slug.
     * @param array  $args Query args used to build the payload.
     *
     * @return string
     */
    private static function build_cache_key(string $taxonomy, array $args): string
    {
        return sprintf(
            '%s:%s',
            $taxonomy,
            md5((string) wp_json_encode($args))
        );
    }

    /**
     * Build the per-taxonomy cache bucket key to allow invalidation for all variants of that taxonomy.
     *
     * @param string $taxonomy The taxonomy slug.
     *
     * @return string
     */
    private static function get_taxonomy_cache_key(string $taxonomy): string
    {
        return sprintf('planet4-taxonomy-list:%s', sanitize_key($taxonomy));
    }

    /**
     * Invalidate the taxonomy list cache for a given taxonomy.
     *
     * @return void
     */
    public static function invalidate_taxonomy_cache(...$args): void
    {
        $taxonomy = $args[2] ?? '';

        if (! is_string($taxonomy) && ! is_int($taxonomy)) {
            return;
        }

        $taxonomy = sanitize_key((string) $taxonomy);

        if ('' === $taxonomy) {
            return;
        }

        wp_cache_delete(self::get_taxonomy_cache_key($taxonomy));
    }
}

add_action('created_term', [\P4\MasterTheme\Api\TaxonomyList::class, 'invalidate_taxonomy_cache'], 10, 3);
add_action('edited_term', [\P4\MasterTheme\Api\TaxonomyList::class, 'invalidate_taxonomy_cache'], 10, 3);
add_action('delete_term', [\P4\MasterTheme\Api\TaxonomyList::class, 'invalidate_taxonomy_cache'], 10, 4);
