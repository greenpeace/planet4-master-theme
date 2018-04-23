<?php

namespace P4ML\Controllers\Tab;

use P4ML\Views\View;
use P4ML\Controllers\MediaLibraryApi_Controller;
use P4ML\Controllers\Search_Controller;

if ( ! class_exists( 'GPI_Media_Library_Controller' ) ) {

	/**
	 * Class Search_Controller
	 *
	 * @package P4ML\Controllers\Tab
	 */
	class GPI_Media_Library_Controller extends Controller {

		/**
		 * Creates the plugin's loader object.
		 * Checks requirements and if its ok it hooks the hook_plugin method on the 'init' action which fires
		 * after WordPress has finished loading but before any headers are sent.
		 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
		 *
		 * @param string $view_class The View class name.
		 */
		public function __construct( View $view ) {
			parent::__construct( $view );

			add_filter( 'media_upload_tabs', [ $this, 'media_library_tab' ] );
			add_action( 'media_upload_gpi_media_library', [ $this, 'add_library_form' ] );

//			add_action( 'wp_ajax_get_paged_posts',        [ 'Search_Controller', 'get_paged_posts' ] );
//			add_action( 'wp_ajax_nopriv_get_paged_posts', [ 'Search_Controller', 'get_paged_posts' ] );
		}

		/**
		 * Add GPI Media Library tab in WP media popup.
		 *
		 * @param array $tabs The associative array with media library tab settings menus.
		 *
		 * @return array
		 */
		public function media_library_tab( $tabs ) {
			$tabs['gpi_media_library'] = 'GPI Media Library';

			return $tabs;
		}

		/**
		 * Add wp_iframe in GPI Media Library popup.
		 */
		public function add_library_form() {
			wp_iframe( [ $this, 'library_form' ] );
		}

		/**
		 * Fetch the data from GP media library and pass to wp_iframe.
		 */
		public function library_form() {
			$ml_api        = new MediaLibraryApi_Controller();
			$image_list    = $ml_api->get_results();

			$this->view->ml_view( [
				'data' => [
					'image_list' => $image_list ,
					'domain'     => 'planet4-medialibrary',
				],
			] );
		}

		/**
		 * Validate file already exist in WP media or not.
		 *
		 * @param string $filename The file name (without full path).
		 *
		 * @return int
		 */
		protected function validate_file_exists( $filename ) {
			global $wpdb;

			$statement = $wpdb->prepare( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_value` LIKE %s", '%' . $filename . '%' );
			$result    = $wpdb->get_col( $statement );

			return $result[0] ?? '';
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
