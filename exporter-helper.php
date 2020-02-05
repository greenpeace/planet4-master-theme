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

	$placeholders = [];
	for ( $i = 2; $i < count( $post_ids ) + 2; $i++ ) {
		$placeholders[] = "%$i\$d";
	}
	$placeholders = implode( ',', $placeholders );

	/**
	 * Post thumbnails
	 */
	if ( $post_ids ) {
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

		$blocks = parse_blocks( $text );
		foreach ( $blocks as $block ) {

			// Fetch the attachement id/s from block fields.
			switch ( $block['blockName'] ) {

				case 'planet4-blocks/enform':
					$attachment_ids[] = $block['attrs']['background'] ?? '';
					break;

				case 'planet4-blocks/happypoint':
					$attachment_ids[] = $block['attrs']['id'] ?? '';
					break;

				case 'planet4-blocks/media-video':
					$attachment_ids[] = $block['attrs']['video_poster_img'] ?? '';
					break;

				case 'planet4-blocks/gallery':
					if ( isset( $block['attrs']['multiple_image'] ) ) {
						$multiple_images = explode( ',', $block['attrs']['multiple_image'] );
						$attachment_ids  = array_merge( $attachment_ids, $multiple_images );
					}
					break;

				case 'planet4-blocks/carousel-header':
					if ( isset( $block['attrs']['slides'] ) ) {
						foreach ( $block['attrs']['slides'] as $slide ) {
							$attachment_ids[] = $slide['image'];
						}
					}
					break;

				case 'planet4-blocks/split-two-columns':
					$attachment_ids[] = $block['attrs']['issue_image'] ?? '';
					$attachment_ids[] = $block['attrs']['tag_image'] ?? '';
					break;

				case 'planet4-blocks/columns':
					if ( isset( $block['attrs']['columns'] ) ) {
						foreach ( $block['attrs']['columns'] as $column ) {
							$attachment_ids[] = $column['attachment'];
						}
					}
					break;

				case 'planet4-blocks/social-media-cards':
					if ( isset( $block['attrs']['cards'] ) ) {
						foreach ( $block['attrs']['cards'] as $card ) {
							$attachment_ids[] = $card['image_id'];
						}
					}
					break;

				case 'planet4-blocks/take-action-boxout':
					$attachment_ids[] = $block['attrs']['background_image'] ?? '';
					break;

				case 'core/image':
					$attachment_ids[] = $block['attrs']['id'] ?? '';
					break;
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
