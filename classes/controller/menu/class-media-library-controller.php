<?php

namespace P4ML\Controllers\Menu;

use P4ML\Helpers\MediaHelper;
use P4ML\Views\View;
use P4ML\Controllers\MediaLibraryApi_Controller;

if ( ! class_exists( 'Media_Library_Controller' ) ) {

	/**
	 * Class Media_Library_Controller
	 *
	 * @package P4ML\Controllers\Menu
	 */
	class Media_Library_Controller extends Controller {

		/**
		 * Creates the plugin's loader object.
		 * Checks requirements and if its ok it hooks the hook_plugin method on the 'init' action which fires
		 * after WordPress has finished loading but before any headers are sent.
		 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
		 *
		 * @param View $view_class The View class Object.
		 */
		public function __construct( View $view ) {
			parent::__construct( $view );
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
					[ $this, 'prepare_ml_search' ],
					P4ML_ADMIN_DIR . 'images/logo_menu_page_16x16.png'
				);
			}
		}

		/**
		 * Pass all needed data to the view object for the main page.
		 */
		public function prepare_ml_search() {
			$image_id      = 'GP0STPTOM';
			$image_details = [
				'image_url'   => '',
				'image_title' => '',
			];

			$ml_api = new MediaLibraryApi_Controller();
			$image_details = $ml_api->get_results( [ 'search_text' => $image_id ] );

			$image_details = $image_details[0];

			if ( '' !== $image_details['image_url'] ) {
				$helper        = new MediaHelper();
				$is_file_exist = $helper->file_exists( basename( $image_details['image_url'] ) );
			}

			if ( $image_details['image_url'] && '' === $is_file_exist ) {
				$file     = $image_details['image_url'];
				$filename = basename( $file );

				$context = stream_context_create( [
						'ssl' => [
							'verify_peer'      => false,
							'verify_peer_name' => false,
						],
					]
				);

				// Upload file into WP upload dir.
				$upload_file = wp_upload_bits( $filename, null, file_get_contents( $file , FALSE, $context ) );

				if ( ! $upload_file['error'] ) {
					$wp_filetype = wp_check_filetype( $filename, null );

					// Prepare an array of post data for the attachment.
					$attachment = [
						'post_mime_type' => $wp_filetype['type'],
						'post_title'     => preg_replace( '/\.[^.]+$/', '', $image_details['image_title'] ),
						'post_content'   => $image_details['image_caption'],
						'post_status'    => 'inherit',
						'post_excerpt'   => $image_details['image_caption'],
					];

					$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], 0, true );

					if ( ! is_wp_error( $attachment_id ) ) {
						require_once( ABSPATH . 'wp-admin/includes/image.php' );

						// Generate the metadata for the attachment, and update the database record.
						$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );

						wp_update_attachment_metadata( $attachment_id,  $attachment_data );

						// Set the image Alt-Text & image Credit.
						update_post_meta( $attachment_id, '_wp_attachment_image_alt', $image_details['image_title'] );
						update_post_meta( $attachment_id, '_credit_text', $image_details['image_credit'] );
					}
				} else {
					$this->error( __( 'Error while uploading file...!', 'planet4-medialibrary' ) );
				}
			}

			$this->view->pages( [
				'data' => [
					'gpml_image_id' => $image_id,
					'wp_image_id'   => '' === $is_file_exist ? $attachment_id : $is_file_exist,
					'image_title'   => $image_details['image_title'],
					'image_url'     => $image_details['image_url'],
					'is_file_exist' => $is_file_exist,
					'domain'        => 'planet4-medialibrary',
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
