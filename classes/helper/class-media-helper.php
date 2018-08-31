<?php

namespace P4ML\Helpers;

use P4ML\Api\MediaImage;

/**
 * A class that contains methods for WP Media.
 *
 * @package P4ML\Helpers
 */
class MediaHelper {

	/**
	 * @param MediaImage $image
	 * @param int        $media_details_flag 1 = Default Title & Description, 2 = Original language Title & Description.
	 *
	 * @return mixed
	 */
	public function upload_file( MediaImage $image, $media_details_flag = 1 ) {
		$url = $image->getPathTr1();

		$file     = $url;
		$filename = basename( $file );

		$context = stream_context_create( [
				'ssl' => [
					'verify_peer'      => false,
					'verify_peer_name' => false,
				],
			]
		);

		// Upload file into WP upload dir.
		$upload_file = wp_upload_bits( $filename, null, file_get_contents( $url, false, $context ) );

		if ( ! $upload_file['error'] ) {
			$wp_filetype = wp_check_filetype( $filename, null );

			if ( 1 === $media_details_flag ) {
				// Prepare an array of post data for the attachment.
				$attachment = [
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', $image->getTitle() ),
					'post_content'   => $image->getCaption(),
					'post_status'    => 'inherit',
					'post_excerpt'   => $image->getCaption(),
				];

				// Check title has fullstop at the end, if not then add it.
				$alt_text = '.' === substr( $image->getTitle(), -1 ) ? $image->getTitle() : $image->getTitle() . '.';
			} else {
				// Prepare an array of post data for the attachment.
				$attachment = [
					'post_mime_type' => $wp_filetype['type'],
					'post_title'     => preg_replace( '/\.[^.]+$/', '', $image->getOriginalLanguageTitle() ),
					'post_content'   => $image->getOriginalLanguageDesc(),
					'post_status'    => 'inherit',
					'post_excerpt'   => $image->getOriginalLanguageDesc(),
				];

				// Check title has fullstop at the end, if not then add it.
				$alt_text = '.' === substr( $image->getOriginalLanguageTitle(), -1 ) ? $image->getOriginalLanguageTitle() : $image->getOriginalLanguageTitle() . '.';
			}

			$attachment_id = wp_insert_attachment( $attachment, $upload_file['file'], 0, true );

			if ( ! is_wp_error( $attachment_id ) ) {
				require_once( ABSPATH . 'wp-admin/includes/image.php' );

				// Generate the metadata for the attachment, and update the database record.
				$attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );

				wp_update_attachment_metadata( $attachment_id, $attachment_data );

				// Add credit to alt field.
				$alt_text = '' !== $image->getCredit() ? $alt_text . ' © ' . $image->getCredit() : $alt_text;

				// Set the image Alt-Text & image Credit.
				update_post_meta( $attachment_id, '_wp_attachment_image_alt', $alt_text );
				update_post_meta( $attachment_id, '_credit_text', $image->getCredit() );

				return $attachment_id;
			} else {
				return __( 'Error while inserting attachment...!', 'planet4-medialibrary' );
			}
		} else {
			return __( 'Error while uploading file...!', 'planet4-medialibrary' );
		}


	}

	/**
	 * Validate file already exist in WP media, if yes then return image id.
	 *
	 * @param string $filename The file name (without full path).
	 *
	 * @return int
	 */
	public function file_exists( $filename ) {
		global $wpdb;

		$statement = $wpdb->prepare( "SELECT `post_id` FROM `{$wpdb->postmeta}` WHERE `meta_value` LIKE %s", '%' . $filename . '%' );
		$result    = $wpdb->get_col( $statement );

		return $result[0] ?? '';
	}
}
