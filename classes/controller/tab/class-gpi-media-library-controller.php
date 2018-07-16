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

		const SHOW_SCROLL_TIMES = 2;

		/**
		 * Creates the plugin's loader object.
		 * Checks requirements and if its ok it hooks the hook_plugin method on the 'init' action which fires
		 * after WordPress has finished loading but before any headers are sent.
		 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
		 *
		 * @param View $view The View class object.
		 */
		public function __construct( View $view ) {
			parent::__construct( $view );

			add_filter( 'media_upload_tabs',                    [ $this, 'media_library_tab' ] );
			add_action( 'media_upload_gpi_media_library',       [ $this, 'add_library_form' ] );
			add_action( 'wp_ajax_download_images_from_library', [ $this, 'download_images_from_library' ] );
			add_action( 'wp_ajax_get_paged_medias',             [ $this, 'get_paged_medias' ] );
			add_action( 'wp_ajax_get_search_medias',            [ $this, 'get_search_medias' ] );
			add_action( 'post-upload-ui',                       [ $this, 'media_library_post_upload_ui' ] );
		}

		/**
		 * Add GPI Media Library upload button in WP media popup upload UI.
		 */
		function media_library_post_upload_ui() {
			$this->load_ml_assets();
			print '<button id="db-upload-btn" class="button media-button button-primary button-large switchtoml">' . esc_attr( __( 'Upload From GPI Media Library', 'planet4-medialibrary' ) ) . '</button>';
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
			$ml_api     = new MediaLibraryApi_Controller();
			$image_list = $ml_api->get_results();

			$error_message = '';
			if ( \WP_Http::OK !==  $image_list['status_code'] ) {
				$error_message = __('Error while fetching data from remote server!!!', 'planet4-medialibrary');
			}

			$this->load_iframe_assets();
			$this->view->ml_view( [
				'data' => [
					'image_list'    => $image_list['result'],
					'error_message' => $error_message,
					'domain'        => 'planet4-medialibrary',
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
				$paged        = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_STRING );
				$query_string = filter_input( INPUT_GET, 'query-string', FILTER_SANITIZE_STRING );

				$ml_api = new MediaLibraryApi_Controller();

				$params = [];

				if ( '' !== $query_string ) {
					$params['search_text'] = $query_string;
				}
				if ( '' !== $paged ) {
					$params['pagenumber'] = $paged;
				}

				$image_list = $ml_api->get_results( $params );

				$error_message = '';
				if ( \WP_Http::OK !==  $image_list['status_code'] ) {
					$error_message = __('Error while fetching data from remote server!!!', 'planet4-medialibrary');
				}

				$this->view->ml_search_view( [
					'data' => [
						'image_list'    => $image_list['result'],
						'error_message' => $error_message,
						'domain'        => 'planet4-medialibrary',
					],
				] );

				wp_die();
			}
		}

		/**
		 * Callback for scroll the next results & search.
		 * Gets the paged/searched medias that belong to the next page & search result and are to be used with the twig template.
		 */
		public function get_search_medias() {
			// If this is an ajax call.
			if ( wp_doing_ajax() ) {
				$paged        = filter_input( INPUT_GET, 'paged', FILTER_SANITIZE_STRING );
				$query_string = filter_input( INPUT_GET, 'query-string', FILTER_SANITIZE_STRING );
				$search_flag  = filter_input( INPUT_GET, 'search_flag', FILTER_SANITIZE_STRING );

				$ml_api = new MediaLibraryApi_Controller();

				$params = [];

				if ( '' !== $query_string ) {
					$params['search_text'] = $query_string;
				}
				if ( '' !== $paged ) {
					$params['pagenumber'] = $paged;
				}

				$image_list = $ml_api->get_results( $params );

				$error_message = '';
				if ( \WP_Http::OK !== $image_list['status_code'] ) {
					$error_message = __( 'Error while fetching data from remote server!!!', 'planet4-medialibrary' );
				}

				if ( 'true' === $search_flag ) {
					$this->view->ml_search_media_view( [
						'data' => [
							'image_list'    => $image_list['result'],
							'error_message' => $error_message,
							'domain'        => 'planet4-medialibrary',
						],
					] );
				} else {
					$this->view->ml_media_view( [
						'data' => [
							'image_list'    => $image_list['result'],
							'error_message' => $error_message,
							'domain'        => 'planet4-medialibrary',
						],
					] );
				}

				wp_die();
			}
		}

		/**
		 * Load assets only on the search page.
		 */
		public function load_iframe_assets() {
			$nonce = wp_create_nonce( 'gpi-media-library-nonce' );

			$params = [
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => $nonce,
				'show_scroll_times' => self::SHOW_SCROLL_TIMES,
			];
			wp_enqueue_style( 'p4ml_admin_style', P4ML_ADMIN_DIR . 'css/admin.css', array(), '0.4' );
			wp_register_script( 'p4ml_admin_script', P4ML_ADMIN_DIR . 'js/adminml.js', array(), '0.5', true );
			wp_localize_script( 'p4ml_admin_script', 'media_library_params', $params );
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-selectable' );
			wp_enqueue_script( 'p4ml_admin_script' );
		}

		/**
		 * Load assets only for the Media library popup.
		 */
		public function load_ml_assets() {
			$nonce = wp_create_nonce( 'gpi-media-library-nonce' );

			$params = [
				'ajaxurl'           => admin_url( 'admin-ajax.php' ),
				'nonce'             => $nonce,
				'show_scroll_times' => self::SHOW_SCROLL_TIMES,
			];
			wp_enqueue_style( 'p4ml_admin_style', P4ML_ADMIN_DIR . 'css/admin_search_ml.css', array(), '0.1' );
			wp_register_script( 'p4ml_admin_script', P4ML_ADMIN_DIR . 'js/admin_search_ml.js', array(), '0.2', true );
			wp_localize_script( 'p4ml_admin_script', 'media_library_params', $params );
			wp_enqueue_script( 'p4ml_admin_script' );
		}

		/**
		 * Action for admin-ajax to be used from gpi media library iframe.
		 */
		public function download_images_from_library() {
			$ml_api          = new MediaLibraryApi_Controller();
			$helper          = new MediaHelper();
			$selected_images = filter_input( INPUT_GET, 'images', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

			$response = [
				'errors' => [],
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
