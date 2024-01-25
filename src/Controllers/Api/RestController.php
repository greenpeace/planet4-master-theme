<?php

/**
 * REST Controller Class
 */

namespace P4\MasterTheme\Controllers\Api;

/**
 * WP REST API interface.
 */
class RestController
{
    /**
     * Initialize class if all checks are ok.
     */
    public function load(): void
    {
        // If WP REST API is not enabled, exit.
        if (! defined('REST_API_VERSION')) {
            return;
        }

        // Need at least REST API version 2.
        if (version_compare(REST_API_VERSION, '2.0', '<')) {
            return;
        }

        $this->set_rest_hooks();
    }

    /**
     * Action for the wp rest api initialization.
     */
    private function set_rest_hooks(): void
    {
        add_action('rest_api_init', [$this, 'setup_rest']);
    }

    /**
     * Setup rest endpoints if REST_REQUEST is defined.
     */
    public function setup_rest(): void
    {
        if (! defined('REST_REQUEST') || ! REST_REQUEST) {
            return;
        }
        $this->setup_rest_endpoints();
    }

    /**
     * Setup the REST endpoints for en plugin.
     */
    private function setup_rest_endpoints(): void
    {
        $version = 'v1';

        $questions_controller = new QuestionsController();

        /**
         * Get a single form's questions.
         *
         * Requires authentication.
         *
         * @route   wp-json/planet4-engaging-networks/v1/questions_available
         * @method  \WP_REST_Server::READABLE (GET)
         *
         * @returns \WP_REST_Response
         */
        register_rest_route(
            P4_REST_SLUG . '/' . $version,
            '/questions_available',
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$questions_controller, 'get_available_questions'],
                'permission_callback' => [$this, 'is_allowed'],
            ]
        );

        /**
         * Get a single form's questions.
         *
         * Requires authentication.
         *
         * @route   wp-json/planet4-engaging-networks/v1/questions
         * @method  \WP_REST_Server::READABLE (GET)
         *
         * @returns \WP_REST_Response
         */
        register_rest_route(
            P4_REST_SLUG . '/' . $version,
            '/questions',
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$questions_controller, 'get_questions'],
                'permission_callback' => [$this, 'is_allowed'],
            ]
        );

        /**
         * Add a single location.
         *
         * Requires authentication.
         *
         * @route   wp-json/planet4-engaging-networks/<v2+>/questions
         * @method  \WP_REST_Server::EDITABLE (POST, PUT, PATCH)
         *
         * @params  int     id          required , question id.
         * @params  string  label       required, question label.
         * @params  string  name        required, question name.
         * @params  string  type        required, specify question's type.
         *
         * @returns \WP_REST_Response
         */
        register_rest_route(
            P4_REST_SLUG . '/' . $version,
            '/questions',
            [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$questions_controller, 'add_question'],
                'permission_callback' => [$this, 'is_allowed'],
            ]
        );

        /**
         * Get a single form's questions.
         *
         * Requires authentication.
         *
         * @route   wp-json/planet4-engaging-networks/<v1+>/questions
         * @method  \WP_REST_Server::READABLE (GET)
         *
         * @returns \WP_REST_Response
         */
        register_rest_route(
            P4_REST_SLUG . '/' . $version,
            '/questions/(?P<id>\d+)',
            [
                'methods' => \WP_REST_Server::READABLE,
                'callback' => [$questions_controller, 'get_question'],
                'permission_callback' => [$this, 'is_allowed'],
            ]
        );

        /**
         * Update a single location.
         *
         * Requires authentication.
         *
         * @route   wp-json/planet4-engaging-networks/v1/questions/<id>
         * @method  \WP_REST_Server::EDITABLE (POST, PUT, PATCH)
         *
         * @params  int     id          required , question id.
         * @params  string  label       required, question label.
         * @params  string  name        required, question name.
         * @params  string  type        required, specify question's type.
         *
         * @returns \WP_REST_Response
         */
        register_rest_route(
            P4_REST_SLUG . '/' . $version,
            '/questions/(?P<id>\d+)',
            [
                'methods' => \WP_REST_Server::EDITABLE,
                'callback' => [$questions_controller, 'update_question'],
                'permission_callback' => [$this, 'is_allowed'],
            ]
        );

        /**
         * Delete a single location.
         *
         * Requires authentication.
         *
         * @route   wp-json/planet4-engaging-networks/v1/questions/<id>
         * @method  \WP_REST_Server::DELETABLE (DELETE)
         *
         * @returns \WP_REST_Response
         */
        register_rest_route(
            P4_REST_SLUG . '/' . $version,
            '/questions/(?P<id>\d+)',
            [
                'methods' => \WP_REST_Server::DELETABLE,
                'callback' => [$questions_controller, 'delete_question'],
                'permission_callback' => [$this, 'is_allowed'],
            ]
        );
    }

    /**
     * Check if user is allowed to access api routes.
     */
    public function is_allowed(): bool
    {
        return current_user_can('manage_options');
    }
}
