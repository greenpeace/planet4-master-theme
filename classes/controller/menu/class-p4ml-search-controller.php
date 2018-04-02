<?php

namespace P4ML\Controllers\Menu;

use P4ML\Views\P4ML_View;

if ( ! class_exists( 'P4ML_Search_Controller' ) ) {

	/**
	 * Class P4ML_Pages_Standard_Controller
	 */
	class P4ML_Search_Controller extends P4ML_Controller {


		/**
		 * Creates the plugin's loader object.
		 * Checks requirements and if its ok it hooks the hook_plugin method on the 'init' action which fires
		 * after WordPress has finished loading but before any headers are sent.
		 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
		 *
		 * @param array $services The Controller services to inject.
		 * @param string $view_class The View class name.
		 */
		public function __construct( P4ML_View $view ) {
			parent::__construct( $view );
//			$this->services = $services;
//			$view = new $view_class();

			// add the tab.
			//add_filter('media_upload_tabs', [$this, 'media_library_tab']);

			// call the new tab with wp_iframe.
			//add_action('media_upload_gpi_media_library', [$this, 'add_library_form']);
		}


		public function media_library_tab( $tabs ) {
			$tabs['gpi_media_library'] = 'Gpi Media Library';

			return $tabs;
		}

		public function add_library_form() {
			wp_iframe( [ $this, 'library_form' ] );
		}


		/**
		 * Tab content.
		 *
		 */
		public function library_form() {
			wp_enqueue_script( 'p4ml_admin_script', P4ML_ADMIN_DIR . 'js/adminml.js', array(), '0.1', true );
			echo media_upload_header(); // This function is used for print media uploader headers etc.
			echo '';
		}

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
					array( $this, 'prepare_pages' ),
					P4ML_ADMIN_DIR . 'images/logo_menu_page_16x16.jpg'
				);
			}
		}

		/**
		 * Pass all needed data to the view object for the main page.
		 */
		public function prepare_pages() {
			$this->view->pages( [
				'pages' => [],
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