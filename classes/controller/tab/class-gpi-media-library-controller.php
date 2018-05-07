<?php

namespace P4ML\Controllers\Tab;

use P4ML\Api\MediaImageMapper;
use P4ML\Controllers\MediaLibraryApi_Controller;
use P4ML\Helpers\MediaHelper;
use P4ML\Views\View;

if ( ! class_exists( 'GPI_Media_Library_Controller' ) ) {

	/**
	 * Class Search_Controller
	 *
	 * @package P4ML\Controllers\Tab
	 */
	class GPI_Media_Library_Controller extends Controller {

		const MEDIAS_LIMIT      = 300;
		const MEDIAS_PER_PAGE   = 10;
		const MEDIAS_PER_LOAD   = 5;
		const SHOW_SCROLL_TIMES = 2;

		/** @var array $localizations */
		protected $localizations;

		/**
		 * Creates the plugin's loader object.
		 * Checks requirements and if its ok it hooks the hook_plugin method on the 'init' action which fires
		 * after WordPress has finished loading but before any headers are sent.
		 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
		 *
		 * @param View $view_class The View class object.
		 */
		public function __construct( View $view ) {
			parent::__construct( $view );

			add_filter( 'media_upload_tabs',                    [ $this, 'media_library_tab' ] );
			add_action( 'media_upload_gpi_media_library',       [ $this, 'add_library_form' ] );
			add_action( 'wp_ajax_download_images_from_library', [ $this, 'download_images_from_library' ] );
			add_action( 'wp_ajax_get_paged_medias',             [ $this, 'get_paged_medias' ] );

			$this->localizations = [
				'show_scroll_times' => self::SHOW_SCROLL_TIMES,
			];
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
			$this->ml_enqueue_public_assets();

			$ml_api     = new MediaLibraryApi_Controller();
			$image_list = $ml_api->get_results();

			$this->load_iframe_assets();
			$this->view->ml_view( [
				'data' => [
					'image_list' => $image_list,
					'domain'     => 'planet4-medialibrary',
				],
			] );
		}

		/**
		 * Callback for loadmore the next results & search.
		 * Gets the paged medias that belong to the next page/loadmore & search result and are to be used with the twig template.
		 */
		public function get_paged_medias() {
			// If this is an ajax call.
			if ( wp_doing_ajax() ) {
				$search_action = filter_input( INPUT_GET, 'search-action', FILTER_SANITIZE_STRING );
				$paged         = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_STRING );

				$query_string  = filter_input( INPUT_GET, 'query-string', FILTER_SANITIZE_STRING );

				// Check if call action is correct.
				if ( 'get_paged_medias' === $search_action ) {

					$ml_api        = new MediaLibraryApi_Controller();

					$params = [];

					if ( '' !== $query_string ) {
						$params['search_text'] = $query_string;
					}

					$image_list    = $ml_api->get_results( $params );

					$this->view->ml_search_view( [
						'data' => [
							'image_list' => $image_list,
							'domain'     => 'planet4-medialibrary',
						],
					] );
				}

				// Check if call action is correct.
				if ( 'get_searched_medias' === $search_action ) {

					$ml_api        = new MediaLibraryApi_Controller();

					$params = [];

					if ( '' !== $query_string ) {
						$params['search_text'] = $query_string;
					}

					$image_list    = $ml_api->get_results( $params );

					$this->view->ml_search_view( [
						'data' => [
							'image_list' => $image_list,
							'domain'     => 'planet4-medialibrary',
						],
					] );
				}
				wp_die();
			}
		}

		/**
		 * Load assets only on the ml search page.
		 */
		public function ml_enqueue_public_assets() {
			wp_register_script( 'p4ml_admin_script', P4ML_ADMIN_DIR . 'js/ml_search.js', [ 'jquery' ], '0.1', false );
			wp_localize_script( 'p4ml_admin_script', 'localizations', $this->localizations );
			wp_enqueue_script( 'p4ml_admin_script' );
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

		/**
		 * Load assets only on the search page.
		 */
		public function load_iframe_assets() {
			$nonce = wp_create_nonce( 'gpi-media-library-nonce' );

			$params = [
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
				'nonce'   => $nonce,
			];
			wp_enqueue_style( 'p4ml_admin_style', P4ML_ADMIN_DIR . 'css/admin.css', array(), '0.1' );
			wp_register_script( 'p4ml_admin_script', P4ML_ADMIN_DIR . 'js/adminml.js', array(), '0.2', true );
			wp_localize_script( 'p4ml_admin_script', 'media_library_params', $params );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-selectable' );
			wp_enqueue_script( 'p4ml_admin_script' );
		}

		/**
		 * Action for admin-ajax to be used from gpi media library iframe.
		 */
		public function download_images_from_library() {
			$ml_api = new MediaLibraryApi_Controller();
			$helper = new MediaHelper();
			$selected_images = filter_input( INPUT_GET, 'images', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			$response = [
				'errors'  => [],
				'images' => [],
			];
			foreach ( $selected_images as $image ) {
				$image_list = $ml_api->get_single_image( $image );
				if ( is_array( $image_list ) ) {
					$image      = ( new MediaImageMapper() )->get_from_array( $image_list[0] );
					$attachment = $helper->file_exists( $image->getId() );

					if ( empty( $attachment ) ) {
						$attachment_upload = $helper->upload_file( $image );

						if ( is_numeric( $attachment_upload ) ) {
							$image->setWordpressId( $attachment_upload );
							$response['images'][] = $image;
						} else {
							$response['errors'][] = $attachment_upload;
						}
					} else {
						$image->setWordpressId( $attachment );
						$response['images'][] = $image;
					}
				} else {
					$response['errors'][] = $image_list;
				}
			}

			echo wp_json_encode( $response );
			wp_die();
		}
	}
}
