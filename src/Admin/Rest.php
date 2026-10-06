<?php

/**
 * @package P4\MasterTheme\Admin
 */

namespace P4\MasterTheme\Admin;

use WP_REST_Request;
use WP_Term;

/**
 * This class is just a place for add_endpoints to live.
 */
class Rest
{
    private const REST_NAMESPACE = 'planet4/v1';

    /**
     * Initialize class if all checks are ok.
     */
    public function load(): void
    {
        add_action('rest_api_init', function (): void {
            $this->register_published_endpoint();
            $this->register_transient_endpoint();
            $this->register_listing_filters_endpoint();
            $this->register_listing_data_fields();
        });
    }

    /**
     * A lightweight endpoint to get all posts with only id and title.
     */
    private function register_published_endpoint(): void
    {
        register_rest_route(
            self::REST_NAMESPACE,
            '/published',
            [
                [
                    'permission_callback' => [ Published::class, 'permission' ],
                    'methods' => Published::methods(),
                    'callback' => static function ($request) {
                        $api = new Published($request);
                        return $api->response();
                    },
                ],
            ]
        );
    }

    /**
     * Access to transient cache for admin purposes.
     */
    private function register_transient_endpoint(): void
    {
        register_rest_route(
            self::REST_NAMESPACE,
            '/transient',
            [
                [
                    'permission_callback' => [ Transient::class, 'permission' ],
                    'methods' => Transient::methods(),
                    'callback' => static function ($request) {
                        $api = new Transient($request);
                        return $api->response();
                    },
                ],
            ]
        );
    }

    /**
     * A lightweight endpoint to get all the filters for the News and Stories page with only id and name.
     */
    private function register_listing_filters_endpoint(): void
    {
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

    /**
     * Exposes only what the listing page's `PostItem` component renders,
     * so the client can skip `_embed` and its heavy embedded objects.
     */
    private function register_listing_data_fields(): void
    {
        register_rest_field(
            ['post', 'p4_action'],
            'listing_data',
            [
                'get_callback' =>
                    fn ($post, $field, $request) =>
                        $this->get_listing_data_fields($post, $field, $request),
                'schema' => null,
            ]
        );
    }

    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    private function get_listing_data_fields(array $post, string $field, WP_REST_Request $request): array
    {
        $id = $post['id'];
        $breadcrumb_taxonomy = sanitize_key($request->get_param('breadcrumb_taxonomy') ?: 'category');

        return [
            'image' => $this->get_listing_data_field_image($id),
            'author' => $this->get_listing_data_field_author($post),
            'authorOverride' => get_post_meta($id, 'p4_author_override', true),
            'categories' => $this->get_listing_data_field_terms($id, $breadcrumb_taxonomy),
            'tags' => $this->get_listing_data_field_terms($id, 'post_tag'),
        ];
    }

    private function get_listing_data_field_terms(int $post_id, string $taxonomy): array
    {
        if (!$taxonomy || !taxonomy_exists($taxonomy)) {
            return [];
        }

        $terms = get_the_terms($post_id, $taxonomy);
        if (!$terms || is_wp_error($terms)) {
            return [];
        }

        return array_map(
            static function (WP_Term $term): array {
                $link = get_term_link($term);

                return [
                    'id' => $term->term_id,
                    'name' => $term->name,
                    'link' => is_wp_error($link) ? '' : $link,
                ];
            },
            $terms
        );
    }

    private function get_listing_data_field_author(array $post): ?array
    {
        $author_id = (int) $post['author'];

        return $author_id ? [
            'name' => get_the_author_meta('display_name', $author_id),
            'link' => get_author_posts_url($author_id),
        ] : null;
    }

    private function get_listing_data_field_image(int $id): ?array
    {
        $thumb_id = get_post_thumbnail_id($id);
        if (!$thumb_id) {
            return null;
        }

        $full = wp_get_attachment_image_src($thumb_id, 'full');

        return [
            'src' => wp_get_attachment_image_url($thumb_id, 'medium_large'),
            'srcset' => wp_get_attachment_image_srcset($thumb_id, 'medium_large') ?: '',
            'width' => $full[1] ?? null,
            'height' => $full[2] ?? null,
            'alt' => get_post_meta($thumb_id, '_wp_attachment_image_alt', true),
        ];
    }
}
