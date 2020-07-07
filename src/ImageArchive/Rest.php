<?php

namespace P4\MasterTheme\ImageArchive;

use P4\MasterTheme\RemoteCallFailed;
use WP_Http;
use WP_REST_Request;
use WP_REST_Response;

class Rest {
	private const REST_NAMESPACE = 'planet4/v1';

	public static function register_endpoints(): void {
		$fetch_archive_images = static function ( WP_REST_Request $request ) {
			try {
				$api_client = ApiClient::from_cache_or_credentials();
			} catch ( RemoteCallFailed $exception ) {
				return rest_ensure_response( new WP_REST_Response( $exception->getMessage(), WP_Http::INTERNAL_SERVER_ERROR ) );
			}

			$params = [
				'pagenumber' => $request->get_param( 'page' ) ?? 0,
			];

			$search_text = $request->get_param( 'search_text' );
			if ( $search_text ) {
				// todo: avoid repetition of default Mediatype:Image param (maybe value object for query?).
				$params['query'] = '(text:' . $search_text . ') and (Mediatype:Image)';
			}

			$images = $api_client->fetch_images( $params );

			return rest_ensure_response( $images );

		};
		register_rest_route( self::REST_NAMESPACE,
			'image-archive/fetch',
			[ 'methods' => \WP_REST_Server::READABLE, 'callback' => $fetch_archive_images, ] );

		$transfer_to_wordpress = static function ( WP_REST_Request $request ) {
			$json = $request->get_json_params();

			$ids                   = $json['ids'];
			$use_original_language = $json['use_original_language '] ?? false;

			try {
				$api_client = ApiClient::from_cache_or_credentials();
			} catch ( RemoteCallFailed $exception ) {
				return rest_ensure_response( new WP_REST_Response( $exception->getMessage(), WP_Http::INTERNAL_SERVER_ERROR ) );
			}
			$images     = $api_client->get_selection( $ids );

			foreach ( $images as $image ) {
				$image->put_in_wordpress( $use_original_language );
			}

			return rest_ensure_response( new \WP_REST_Response( $images, WP_Http::OK ) );
		};
		register_rest_route( self::REST_NAMESPACE,
			'image-archive/transfer',
			[ 'methods' => \WP_REST_Server::CREATABLE, 'callback' => $transfer_to_wordpress, ] );
	}
}
