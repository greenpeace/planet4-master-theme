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
        });
    }
}
