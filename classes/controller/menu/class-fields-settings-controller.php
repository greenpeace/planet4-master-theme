<?php
/**
 * Fields Settings Controller class.
 *
 * @package P4EN
 */

namespace P4GEN\Controllers\Menu;

if ( ! class_exists( 'Fields_Settings_Controller' ) ) {

	/**
	 * Class Fields_Settings_Controller
	 */
	class Fields_Settings_Controller extends Controller {

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			if ( current_user_can( 'manage_options' ) ) {
				add_submenu_page(
					P4GEN_PLUGIN_SLUG_NAME,
					__( 'Field Settings', 'planet4-gutenberg-engagingnetworks' ),
					__( 'Field Settings', 'planet4-gutenberg-engagingnetworks' ),
					'manage_options',
					'fields-settings',
					[ $this, 'prepare_page' ]
				);
			}
		}

		/**
		 * Pass all needed data to the view object for the page.
		 */
		public function prepare_page() {

			add_action( 'admin_print_footer_scripts', [ $this, 'print_admin_footer_scripts' ], 1 );

			wp_register_script(
				'en-app',
				P4GEN_ADMIN_DIR . '/js/en_app.js',
				[
					'jquery',
					'wp-api',
					'wp-backbone',
				],
				'0.1',
				true
			);
			wp_localize_script(
				'en-app',
				'p4_data',
				[
					'api_url' => get_site_url() . '/wp-json/' . P4_REST_SLUG . '/v1',
					'nonce'   => wp_create_nonce( 'wp_rest' ),
				]
			);
			wp_enqueue_script( 'en-app' );

			$data = [
				'messages' => $this->messages,
			];

			$this->view->view_template( 'fields_settings', $data );
		}

		/**
		 * Load underscore templates to footer.
		 */
		public function print_admin_footer_scripts() {
			$this->view->view_template( 'fields_settings_templates', [] );
		}

		/**
		 * Validates the user input.
		 *
		 * @param array $settings The associative array with the input that the user submitted.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			return true;
		}

		/**
		 * Sanitizes the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 */
		public function sanitize( &$input ) {
		}
	}
}
