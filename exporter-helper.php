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
	$sql = 'SELECT ID, guid, post_parent FROM %1$s WHERE post_type = \'attachment\'';
	$prepared_sql = $wpdb->prepare(
		$sql,
		[
			$wpdb->posts,
		] );
	$attachments = $wpdb->get_results( $prepared_sql, OBJECT_K );
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
		$sql            = 'SELECT meta_value FROM %1$s  WHERE ( meta_key = \'_thumbnail_id\' or meta_key = \'background_image_id\' ) AND post_id IN(' . $placeholders . ')';
		$values         = [];
		$values[0]      = $wpdb->postmeta;
		$values         = array_merge( $values, $post_ids );
		$prepared_sql   = $wpdb->prepare( $sql, $values );     // WPCS: unprepared SQL OK.
		$results        = $wpdb->get_results( $prepared_sql ); // WPCS: unprepared SQL OK.
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

	$placeholders = [];
	for ( $i = 2; $i < count( $post_ids ) + 2; $i++ ) {
		$placeholders[] = "%$i\$d";
	}

	$placeholders = implode( ',', $placeholders );

	$sql = 'SELECT post_content 
			FROM %1$s 
			WHERE ID IN(' . $placeholders . ') 
				AND post_content REGEXP \'((wp-image-|wp-att-)[0-9][0-9]*)|gallery_block_style|wp\:planet4\-blocks|href=|src=\'';

	$values       = [];
	$values[0]    = $wpdb->posts;
	$values       = array_merge( $values, $post_ids );
	$prepared_sql = $wpdb->prepare( $sql, $values );     // WPCS: unprepared SQL OK.
	$results      = $wpdb->get_results( $prepared_sql ); // WPCS: unprepared SQL OK.

	foreach ( (array) $results as $text ) {
		$text = $text->post_content;

		// Filter attachment ids from caption.
		preg_match_all( '#(wp-image-|wp-att-|attachment\_)(\d+)#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$attachment_ids[] = $match[2];
		}

		// Filter attachment ids from shortcake code(shortcake_gallery, shortcake_happy_point, shortcake_media_video).
		preg_match_all( '#wp\:planet4\-blocks\/[a-zA-Z0-9\_\"\'\-\s\:\/\/\=\.\?\&\,\_\{\%]*(multiple_image|background|id|video_poster_img)[\"|\'][\:][\"|\']?([\d\s\,]*)[\"|\']?#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			if ( 'multiple_image' === $match[1] ) {
				$multiple_images = explode( ',', $match[2] );
				$attachment_ids  = array_merge( $attachment_ids, $multiple_images );
			} else {
				$attachment_ids[] = $match[2];
			}
		}

		// Filter attachment ids from shortcake code(shortcake_carousel_header, shortcake_split_two_columns, shortcake_columns).
		preg_match_all( '#[\"|\'](image|attachment|issue_image|tag_image)[\"|\'][\:][\"|\']?([\d]*)[\'|\"]?#', $text, $matches, PREG_SET_ORDER );
		foreach ( $matches as $match ) {
			$attachment_ids[] = $match[2];
		}

		// Filter attachment ids from [gallery] shortcode.
		preg_match_all( '#wp\:gallery\s\{[\"|\'](ids)[\"|\'][\:][\[]([\d\,]*)[\]]#', $text, $matches, PREG_SET_ORDER );
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
