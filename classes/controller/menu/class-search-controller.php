<?php

namespace P4ML\Controllers\Menu;

use P4ML\Views\View;
use P4ML\Controllers\MediaLibraryApi_Controller;

if ( ! class_exists( 'Search_Controller' ) ) {

	/**
	 * Class Search_Controller
	 *
	 * @package P4ML\Controllers\Menu
	 */
	class Search_Controller extends Controller {


		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {
				add_menu_page(
					'Media Library',
					'MediaLibrary',
					'edit_pages',
					P4ML_PLUGIN_SLUG_NAME,
					array( $this, 'prepare_ml_search' ),
					P4ML_ADMIN_DIR . 'images/logo_menu_page_16x16.jpg'
				);
			}
		}

		/**
		 * Pass all needed data to the view object for the main page.
		 */
		public function prepare_ml_search() {

			$ml_api = new MediaLibraryApi_Controller();
			// TO DO : Make it dynamic.
			$main_settings = [
				'p4ml_login_id' => 'kyriakos.diamantis@greenpeace.org',
				'p4ml_password' => 'green23',
			]; //get_option( 'p4ml_main_settings' );

			$image_id    = 'GP0STPTOM';
			$image_url   = '';
			$image_title = '';

			if ( isset( $main_settings['p4ml_login_id'] ) && $main_settings['p4ml_login_id'] && isset( $main_settings['p4ml_password'] ) && $main_settings['p4ml_password'] ) {
				// Check if the authentication API call is cached.
				$ml_auth_token = get_transient( 'ml_auth_token' );

				if ( false !== $ml_auth_token ) {
					$params = [
						'query'  => '(text:' . $image_id . ') and (Mediatype:Image)',
						'fields' => 'Title,Path_TR1,Path_TR1_COMP,Path_TR1_COMP_SMALL',
						'format' => 'json',
						'token'  => $ml_auth_token,
					];

					$response = $ml_api->get_results( $params );

					if ( is_array( $response ) && $response['body'] ) {
						$image_data = json_decode( $response['body'], true );

						if ( isset( $image_data['APIResponse']['Items'][0] ) ) {
							$image_url   = $image_data['APIResponse']['Items'][0]['Path_TR1_COMP_SMALL']['URI'];
							$image_title = $image_data['APIResponse']['Items'][0]['Title'];
						}
					} else {
						$this->error( $response );
					}
				} else {
					$response = $ml_api->authenticate( $main_settings['p4ml_login_id'], $main_settings['p4ml_password'] );

					if ( is_array( $response ) && $response['body'] ) {
						// Communication with ML API is authenticated.
						$body           = json_decode( $response['body'], true );
						$ml_auth_token = $body['APIResponse']['Token'];
						// Time period in seconds to keep the ml_auth_token before refreshing. Typically 1 hour.
						if ( isset( $body['APIResponse']['TimeoutPeriodMinutes'] ) ) {
							$expiration     = ( int ) ( $body['APIResponse']['TimeoutPeriodMinutes'] ) * 60;
						} else {
							$expiration     = 60 * 60; // Default expirations in 1hr.
						}

						set_transient( 'ml_auth_token', $ml_auth_token, $expiration );

						$params = [
							'query'  => '(text:GP0STPTOM) and (Mediatype:Image)',
							'fields' => 'Title,Path_TR1,Path_TR1_COMP,Path_TR1_COMP_SMALL',
							'format' => 'json',
							'token'  => $ml_auth_token,
						];

						$response = $ml_api->get_results( $params );

						if ( is_array( $response ) && $response['body'] ) {
							$image_data = json_decode( $response['body'], true );

							if ( isset( $image_data['APIResponse']['Items'][0] ) ) {
								$image_url   = $image_data['APIResponse']['Items'][0]['Path_TR1_COMP_SMALL']['URI'];
								$image_title = $image_data['APIResponse']['Items'][0]['Title'];
							}
						} else {
							$this->error( $response );
						}
					} else {
						$this->error( $response );
					}
				}
			} else {
				$this->warning( __( 'Plugin Settings are not configured well!', 'planet4-engagingnetworks' ) );
			}

			$this->view->pages( [
				'data' => [
					'image_id'    => $image_id,
					'image_title' => $image_title,
					'image_url'   => $image_url,
				],
			] );
		}

		/**
		 * Validates the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			// TODO: Implement validate() method.
			$has_errors = false;

			return ! $has_errors;
		}

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin (Call by Reference).
		 */
		public function sanitize( &$settings ) {
			// TODO: Implement sanitize() method.
		}
	}
}