<?php

/**
 * @package P4\MasterTheme\Admin
 */

namespace P4\MasterTheme\Admin;

use WP_REST_Request;
use WP_REST_Server;
use WP_Error;

/**
 * Transient cache.
 */
class Transient
{
    private wpdb $db;

    private WP_REST_Request $request;

    /**
     * Constructor
     *
     * @param WP_REST_Request $request Request.
     */
    public function __construct(WP_REST_Request $request)
    {
        global $wpdb;
        $this->db = $wpdb;
        $this->request = $request;
    }

    /**
     * Permission callback
     */
    public static function permission(): bool
    {
        return current_user_can('manage_options');
    }

    /**
     * HTTP Method
     */
    public static function methods(): array
    {
        return [
            WP_REST_Server::READABLE,
            WP_REST_Server::CREATABLE,
        ];
    }

    /**
     * Get API response data.
     *
     * @return WP_REST_Response|WP_Error
     */
    public function response()
    {
        switch ($this->request->get_method()) {
            case 'POST':
                return $this->post_response();
            case 'GET':
                return $this->get_response();
            default:
                return rest_ensure_response(new WP_Error('Method not supported.'));
        }
    }

    /**
     * Post data to transient cache.
     *
     * @return WP_REST_Response|WP_Error
     */
    private function post_response()
    {
        $params = $this->request->get_params();
        $items = $params['items'] ?? [ $params ];

        foreach ($items as $item) {
            set_transient(
                $item['key'],
                $item['value'],
                $item['expiration'] ?? 1200
            );
        }

        return rest_ensure_response([ 'result' => true ]);
    }

    /**
     * Get data from transient cache.
     *
     * @return WP_REST_Response|WP_Error
     */
    private function get_response()
    {
        $params = $this->request->get_params();
        $keys = $params['keys'] ?? [ $params ];

        if (empty($keys)) {
            return rest_ensure_response(new WP_Error('Parameter error.'));
        }

        $items = [];
        foreach ($keys as $key) {
            $items[ $key ] = get_transient($key);
        }

        return rest_ensure_response(
            [ 'items' => $items ]
        );
    }
}
