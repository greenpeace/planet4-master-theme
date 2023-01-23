<?php

namespace P4\MasterTheme\ImageArchive;

use Exception;
use P4\MasterTheme\Capability;
use P4\MasterTheme\Exception\RemoteCallFailed;
use P4\MasterTheme\Features\ImageArchive;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Server;

/**
 * Wrapper class to add some endpoints.
 *
 * @todo: authorization.
 */
class Rest
{
    /**
     * @var string The API path.
     */
    private const REST_NAMESPACE = 'planet4/v1';

    /**
     * Add some REST API endpoints if feature is active.
     */
    public static function register_endpoints(): void
    {
        if (! ImageArchive::is_active()) {
            return;
        }
        $fetch_archive_images = static function (WP_REST_Request $request) {
            try {
                $api_client = ApiClient::from_cache_or_credentials();
            } catch (RemoteCallFailed $exception) {
                return self::handle_authentication_failed($exception);
            }

            $params = [
                'pagenumber' => $request->get_param('page') ?? 0,
            ];

            $search_text = $request->get_param('search_text');
            if ($search_text) {
                // todo: avoid repetition of default Mediatype:Image param (maybe value object for query?).
                $params['query'] = '(text:' . $search_text . ') and (Mediatype:Image)';
            }

            $images = $api_client->fetch_images($params);

            return rest_ensure_response($images);
        };
        register_rest_route(
            self::REST_NAMESPACE,
            'image-archive/fetch',
            [
                'methods' => WP_REST_Server::READABLE,
                'callback' => $fetch_archive_images,
                'permission_callback' => function () {
                    return current_user_can(Capability::USE_IMAGE_ARCHIVE_PICKER);
                },
            ]
        );

        $transfer_to_wordpress = static function (WP_REST_Request $request) {
            $json = $request->get_json_params();

            $ids = $json['ids'];
            $use_original_language = $json['use_original_language '] ?? false;

            try {
                $api_client = ApiClient::from_cache_or_credentials();
            } catch (RemoteCallFailed $exception) {
                return self::handle_authentication_failed($exception);
            }
            $images = $api_client->get_selection($ids);

            foreach ($images as $image) {
                $image->put_in_wordpress($use_original_language);
            }

            return new WP_REST_Response($images, WP_Http::OK);
        };
        register_rest_route(
            self::REST_NAMESPACE,
            'image-archive/transfer',
            [
                'methods' => WP_REST_Server::CREATABLE,
                'callback' => $transfer_to_wordpress,
                'permission_callback' => function () {
                    return current_user_can(Capability::USE_IMAGE_ARCHIVE_PICKER);
                },
            ]
        );
    }

    /**
     * Return an error response if authentication to media API failed.
     *
     * @param Exception $exception Thrown while performing authentication.
     *
     * @return WP_REST_Response HTTP error response.
     */
    private static function handle_authentication_failed(Exception $exception): WP_REST_Response
    {
        return new WP_REST_Response(
            'Failed to authenticate. Error: ' . $exception->getMessage(),
            WP_Http::INTERNAL_SERVER_ERROR
        );
    }
}
