<?php

namespace P4ML\Controllers;

if ( ! class_exists( 'MediaLibraryApi_Controller' ) ) {

	/**
	 * Class MediaLibraryApi_Controller
	 *
	 * @package P4ML\Controllers
	 */
	class MediaLibraryApi_Controller {

		const ENS_BASE_URL = "https://www.media.greenpeace.org";
		const ENS_AUTH_URL = self::ENS_BASE_URL . '/API/Authentication/v1.0/Login';
		const ENS_CALL_TIMEOUT = 10;            // Seconds after which the api call will timeout if not responded.


		/**
		 * Authenticates usage of ENS API calls.
		 *
		 * @param string $ens_private_token The private api token to be used in order to authenticate for ENS API.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function authenticate() {

			$url = self::ENS_AUTH_URL;

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_remote_post( $url, [
				'body'      => [
					'Login'    => '',
					'Password' => '',
					'format'   => 'json',
				],
				'timeout'   => self::ENS_CALL_TIMEOUT,
				'sslverify' => false,

			] );

			// Authentication failure.
			if ( is_wp_error( $response ) ) {
				return $response->get_error_message() . ' ' . $response->get_error_code();

			} elseif ( is_array( $response ) && \WP_Http::ACCEPTED !== $response['response']['code'] ) {
				return $response['response']['message'] . ' ' . $response['response']['code'];

			}

			return $this->decodeResponse( $response['body'] );
		}

		/**
		 * Gets all the information on the available pages built in EN.
		 *
		 * @param string $ens_auth_token The authentication token to be used in all following ENS API calls.
		 * @param array $params The query parameters to be added in the url.
		 *
		 * @return array|string An associative array with the response (under key 'body') or a string with an error message in case of a failure.
		 */
		public function get_results( $token ) {

			$url    = self::ENS_PAGES_URL;
			$params = [
				'query'  => 'Keyword:flowers',
				'fields' => 'Title,Path_TR2',
				'format' => 'json',
				'token'  => $token
			];
			$url    = add_query_arg( $params, $url );

			// With the safe version of wp_remote_{VERB) functions, the URL is validated to avoid redirection and request forgery attacks.
			$response = wp_remote_get( $url, [
				'timeout'   => self::ENS_CALL_TIMEOUT,
				'sslverify' => false
			] );

//			if ( is_wp_error( $response ) ) {
//				return $response->get_error_message() . ' ' . $response->get_error_code();
//
//			} elseif ( is_array( $response ) && \WP_Http::OK !== $response['response']['code'] ) {
//				return $response['response']['message'] . ' ' . $response['response']['code'];         // Authentication failed.
//
//			}
			return $response;
		}

		private function decodeResponse( $resp ) {

			return json_decode( $resp, false );
		}
	}
}
