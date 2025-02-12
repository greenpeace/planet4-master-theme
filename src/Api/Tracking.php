<?php

declare(strict_types=1);

namespace P4\MasterTheme\Api;

use WP_REST_Server;

/**
 * Instance Tracking API
 */
class Tracking
{
    /**
     * Register endpoint to read general Tracking.
     *
     * @example GET /wp-json/planet4/v1/tracking/
     */
    public static function register_endpoint(): void
    {
        register_rest_route(
            'planet4/v1',
            'tracking/login',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => function ($request) {
                    global $wpdb;

                    $last_days = (60 * 60 * 24 * $request->get_param('last_days'));
                    $now = time();

                    $query = $wpdb->get_results(
                        "select * from wp_usermeta as m
                        inner join wp_users as u
                        on m.user_id = u.ID
                        and m.meta_key='session_tokens'",
                        OBJECT
                    );

                    $data = array();
                    foreach ($query as $row) {
                        $unserialized = maybe_unserialize($row->meta_value);
                        $key = array_keys($unserialized)[0];
                        $login_date = $unserialized[$key]['login'];
                        // $obj = ;

                        if (($now - $login_date) >= $last_days) {
                            continue;
                        }

                        array_push(
                            $data,
                            [
                                'display_name' => $row->display_name,
                                // Convert epoch to human-readable date
                                'login_date' => date("Y-m-d H:i:s", $login_date),
                            ]
                        );
                    }

                    return count($data);
                },
                // 'permission_callback' => function ($request) {
                    // return true;
                    // if (!defined('PLANET4_API_KEY') || empty(PLANET4_API_KEY)) {
                    //     return false;
                    // }

                    // $token = $request->get_header('X-Auth-Token');
                    // return !empty($token) && $token === PLANET4_API_KEY;
                // },
                'args' => array(
                    'last_days' => array(
                        'required' => false,
                        'default' => 30,
                    ),
                ),
            ]
        );
    }
}
