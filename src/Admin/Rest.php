<?php

/**
 * @package P4\MasterTheme\Admin
 */

namespace P4\MasterTheme\Admin;

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
            /**
             * A lightweight endpoint to get all posts with only id and title.
             */
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

            /**
             * Access to transient cache for admin purposes.
             */
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

            /**
             * A lightweight endpoint to get all the filters for the News and Stories page with only id and name.
             */
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

            /**
             * Exposes only what the listing page's `PostItem` component renders,
             * so the client can skip `_embed` and its heavy embedded objects.
             */
            register_rest_field(
                ['post', 'p4_action'],
                'listing_data',
                [
                    'get_callback' => function ($post, $field, $request) {
                        $id = $post['id'];

                        $terms_for = function ($taxonomy) use ($id) {
                            if (!$taxonomy || !taxonomy_exists($taxonomy)) {
                                return [];
                            }
                            $terms = get_the_terms($id, $taxonomy);
                            if (!$terms || is_wp_error($terms)) {
                                return [];
                            }
                            $out = [];
                            foreach ($terms as $term) {
                                $link = get_term_link($term);
                                $out[] = [
                                    'id'   => $term->term_id,
                                    'name' => $term->name,
                                    'link' => is_wp_error($link) ? '' : $link,
                                ];
                            }
                            return $out;
                        };

                        // Image
                        $image    = null;
                        $thumb_id = get_post_thumbnail_id($id);
                        if ($thumb_id) {
                            $full  = wp_get_attachment_image_src($thumb_id, 'full');
                            $image = [
                                'src'    => wp_get_attachment_image_url($thumb_id, 'medium_large'),
                                'srcset' => wp_get_attachment_image_srcset($thumb_id, 'medium_large') ?: '',
                                'width'  => $full[1] ?? null,
                                'height' => $full[2] ?? null,
                                'alt'    => get_post_meta($thumb_id, '_wp_attachment_image_alt', true),
                            ];
                        }

                        // Author
                        $author_id = (int) $post['author'];
                        $author    = $author_id ? [
                            'name' => get_the_author_meta('display_name', $author_id),
                            'link' => get_author_posts_url($author_id),
                        ] : null;

                        $breadcrumb_taxonomy = sanitize_key($request->get_param('breadcrumb_taxonomy') ?: 'category');

                        return [
                            'image'          => $image,
                            'author'         => $author,
                            'authorOverride' => get_post_meta($id, 'p4_author_override', true),
                            'categories'     => $terms_for($breadcrumb_taxonomy),
                            'tags'           => $terms_for('post_tag'),
                        ];
                    },
                    'schema' => null,
                ]
            );
        });
    }
}
