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
                    'methods'  => 'GET',
                    'permission_callback' => '__return_true',
                    'callback' => function () {
                        return [
                            'post_types' => get_terms( [ 'taxonomy' => 'p4-page-type', 'hide_empty' => true, 'fields' => 'id=>name' ] ),
                            'categories' => get_terms( [ 'taxonomy' => 'category', 'hide_empty' => true, 'fields' => 'id=>name' ] ),
                            'tags'       => get_terms( [ 'taxonomy' => 'post_tag', 'hide_empty' => true, 'fields' => 'id=>name' ] ),
                        ];
                    },
                ]
            );
        });
    }
}
