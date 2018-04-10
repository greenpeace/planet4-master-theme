<?php

namespace P4ML\Controllers;

if ( ! class_exists( 'MediaLibraryApi_Controller' ) ) {

	/**
	 * Class MediaLibraryApi_Controller
	 *
	 * @package P4ML\Controllers
	 */
	class MediaLibraryApi_Controller {

		const ML_BASE_URL     = "https://www.media.greenpeace.org";
		const ML_AUTH_URL     = self::ML_BASE_URL . '/API/Authentication/v1.0/Login';
		const ML_SEARCH_URL   = self::ML_BASE_URL . '/API/search/v3.0/search';
		const ML_CALL_TIMEOUT = 10;            // Seconds after which the api call will timeout if not responded.


		/**
		 * Authenticates usage of ENS API calls.
		 *
		 * @param string $p4ml_login_id The media library loginID to be used in order to authenticate for ML API.
		 * @param string $p4ml_password The media library password to be used in order to authenticate for ML API.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function authenticate( $p4ml_login_id, $p4ml_password ) {

			$url = self::ML_AUTH_URL;

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_safe_remote_post( $url, [
				'body'      => [
					'Login'    => $p4ml_login_id,
					'Password' => $p4ml_password,
					'format'   => 'json',
				],
				'timeout'   => self::ML_CALL_TIMEOUT,
				'sslverify' => false,

			] );

			// Authentication failure.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::ACCEPTED !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];

			}

			return $response;
		}

		/**
		 * Gets all the information on the available pages built in EN.
		 *
		 * @param array $params The query parameters to be added in the url.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function get_results( $params ) {

			$url    = self::ML_SEARCH_URL;
			$url    = add_query_arg( $params, $url );

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_remote_get( $url, [
				'timeout'   => self::ML_CALL_TIMEOUT,
				'sslverify' => false,
			] );

			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];         // Authentication failed.

			}

			return $response;
		}
	}
}
