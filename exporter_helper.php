<?php
/**
 * Post Exporter Helper Function
 *
 * @package P4MT
 */

/**
 * Returns all attachment ids from campaign post content.
 *
 * @param integer $post_ids Post ID.
 */
function get_campaign_attachments( $post_ids ) {

	global $wpdb;

	$attachments = $wpdb->get_results( "SELECT ID, guid, post_parent FROM {$wpdb->posts} WHERE post_type = 'attachment'", OBJECT_K );
	if ( empty( $attachments ) ) {
		return $post_ids;
	}

	$ids = array();

	/**
	 * Post thumbnails
	 */
	if ( $post_ids ) {
		$ids = $wpdb->get_col( sprintf( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_thumbnail_id' AND post_id IN(%s)", implode( ',', $post_ids ) ) );
	}

	/**
	 * Uploaded to (post_parent)
	 */
	foreach ( $attachments as $id => $att ) {
		if ( in_array( $att->post_parent, $post_ids ) ) {
			$ids[] = $id;
		}
	}

	foreach ( $wpdb->get_col( "SELECT post_content FROM {$wpdb->posts} WHERE ID IN( " . implode( ',', $post_ids ) . " ) AND post_content REGEXP '((wp-image-|wp-att-)[0-9][0-9]*)|\\\[gallery |href=|src='" ) as $text ) {

		// wp-x-ID tags content.
		preg_match_all( '#(wp-image-|wp-att-)(\d+)#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$ids[] = $match[2];
		}

		// shortcake image ID filter.
		preg_match_all( '#\[shortcake\_[a-zA-Z0-9\_\"\'\-\s\:\/\/\=\.]*\s(multiple_image|background|video_poster_img)[=][\"|\']([\d\s\,]*)[\"|\']#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			if ( 'multiple_image' === $match[1] ) {
				$multiple_images = explode( ',', $match[2] );
				$ids             = array_merge( $ids, $multiple_images );
			} else {
				$ids[] = $match[2];
			}
		}

		// filter shortcake_carousel_header, shortcake_split_two_columns.
		preg_match_all( '#\s(image_[0-9]*|issue_image|tag_image)[=][\"|\']([\d\s\,]*)[\"|\']#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$ids[] = $match[2];
		}

		// [gallery] shortcode.
		preg_match_all( '#\[gallery\s+[ids=\"\']+([\d\s,]*)[\"\'].#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			foreach ( explode( ',', $match[1] ) as $id ) {
				$ids[] = (int) $id;
			}
		}
	}

	$ids = array_unique( $ids );
	sort( $ids );

	// The post ids are reorderd as sort all attachment ids first and then append the post id to array.(Added for simplification of import process).
	$ids      = array_diff( $ids, $post_ids );
	$post_ids = array_merge( $ids, $post_ids );

	return $post_ids;
}
