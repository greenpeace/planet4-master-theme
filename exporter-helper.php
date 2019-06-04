<?php
/**
 * Post Exporter Helper Function
 *
 * @package P4MT
 */

/**
 * Returns all attachment ids from campaign post content.
 *
 * @param array $post_ids Post IDs.
 * @return array  $post_ids Post IDs.
 */
function get_campaign_attachments( $post_ids ) {

	global $wpdb;

	// phpcs:disable
	$attachments = $wpdb->get_results( "SELECT ID, guid, post_parent FROM {$wpdb->posts} WHERE post_type = 'attachment'", OBJECT_K );
	// phpcs:enable

	if ( empty( $attachments ) ) {
		return $post_ids;
	}

	$attachment_ids = [];

	/**
	 * Post thumbnails
	 */
	if ( $post_ids ) {
		$placeholders   = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
		$results        = $wpdb->get_results( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE ( meta_key = '_thumbnail_id' or meta_key = 'background_image_id' ) AND post_id IN($placeholders)", $post_ids ) ); // phpcs:ignore
		$attachment_ids = [];
		foreach ( (array) $results as $result ) {
			$attachment_ids[] = $result->meta_value;
		}
	}

	/**
	 * Uploaded to (post_parent)
	 */
	foreach ( $attachments as $id => $att ) {
		if ( in_array( $att->post_parent, $post_ids, true ) ) {
			$attachment_ids[] = $id;
		}
	}

	$placeholders = implode( ',', array_fill( 0, count( $post_ids ), '%d' ) );
	$results      = $wpdb->get_results( $wpdb->prepare( "SELECT post_content FROM {$wpdb->posts} WHERE ID IN($placeholders) AND post_content REGEXP '((wp-image-|wp-att-)[0-9][0-9]*)|\\\[gallery |shortcake\_|href=|src='", $post_ids ) ); // phpcs:ignore

	foreach ( (array) $results as $text ) {
		$text = $text->post_content;

		// Filter attachment ids from caption.
		preg_match_all( '#(wp-image-|wp-att-|attachment\_)(\d+)#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$attachment_ids[] = $match[2];
		}

		// Filter attachment ids from shortcake code(shortcake_gallery, shortcake_happy_point, shortcake_media_video).
		preg_match_all( '#\[shortcake\_[a-zA-Z0-9\_\"\'\-\s\:\/\/\=\.\?\&]*\s(multiple_image|background|video_poster_img)[=][\"|\']([\d\s\,]*)[\"|\']#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			if ( 'multiple_image' === $match[1] ) {
				$multiple_images = explode( ',', $match[2] );
				$attachment_ids  = array_merge( $attachment_ids, $multiple_images );
			} else {
				$attachment_ids[] = $match[2];
			}
		}

		// Filter attachment ids from shortcake code(shortcake_carousel_header, shortcake_split_two_columns, shortcake_columns).
		preg_match_all( '#\s(image_[0-9]*|attachment_[0-9]*|issue_image|tag_image)[=][\"|\']([\d\s\,]*)[\"|\']#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$attachment_ids[] = $match[2];
		}

		// Filter attachment ids from [gallery] shortcode.
		preg_match_all( '#\[gallery\s+[ids=\"\']+([\d\s,]*)[\"\'].#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			foreach ( explode( ',', $match[1] ) as $id ) {
				$attachment_ids[] = (int) $id;
			}
		}
	}

	$attachment_ids = array_unique( $attachment_ids );
	sort( $attachment_ids );

	// The post ids are reorderd as sort all attachment ids first and then append the post id to array.(Added for simplification of import process).
	$attachment_ids = array_diff( $attachment_ids, $post_ids );
	$post_ids       = array_merge( $attachment_ids, $post_ids );

	return $post_ids;
}
