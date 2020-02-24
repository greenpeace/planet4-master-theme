<?php
/**
 * Post Exporter Helper Function
 *
 * @package P4MT
 */
/**
 * Generate a bunch of placeholders for use in an IN query.
 * Such a shame WordPress doesn't offer a decent way to do bind IN statement params and we have to do this ourselves.
 *
 * @param array $items The items to generate placeholders for.
 * @param int $start_index The start index to use for creating the placeholders.
 * @return string The generated placeholders string.
 */
function generate_list_placeholders(array $items, int $start_index): string {
	$placeholders   = [];
	foreach ( range( $start_index, count( $items ) + $start_index - 1 ) as $i ) {
		$placeholders[] = "%$i\$d";
	}
	return implode( ',', $placeholders );
}

/**
 * Parse the post content and return all attachment ids used in blocks.
 *
 * @param string $content The content to parse.
 * @return array All attachments used in the blocks.
 */
function get_attachments_used_in_content( string $content ): array {
	$blocks = parse_blocks( $content );

	$attachment_ids = [];

	foreach ( $blocks as $block ) {

		// Fetch the attachement id/s from block fields.
		switch ( $block['blockName'] ) {

			case 'planet4-blocks/enform':
				$attachment_ids[] = $block['attrs']['background'] ?? '';
				break;

			case 'core/image':
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

		}
	}

	return $attachment_ids;
}

/**
 * Returns all attachment ids from campaign post content.
 *
 * @param array $post_ids Post IDs.
 * @return array  $post_ids Post IDs.
 */
function get_campaign_attachments( $post_ids ) {

	global $wpdb;

	if ( empty($post_ids) ) {
		return [];
	}

	// phpcs:disable
	$sql            = '
SELECT id
FROM wp_posts
WHERE post_type = \'attachment\'
AND parent_post_id post_parent IN (' . generate_list_placeholders( $post_ids, 1 ) . ')';

	$prepared_sql   = $wpdb->prepare( $sql, $post_ids );
	$attachment_ids = $wpdb->get_results( $prepared_sql, OBJECT_K );
	// phpcs:enable

	/**
	 * Post thumbnails
	 */
	$sql          = '
SELECT meta_value
FROM wp_postmeta
WHERE ( meta_key = \'_thumbnail_id\' OR meta_key = \'background_image_id\' )
AND post_id IN(' . generate_list_placeholders( $post_ids, 1 ) . ')';

	$prepared_sql = $wpdb->prepare(
		$sql,
		$post_ids
	); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$results      = $wpdb->get_results( $prepared_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	$attachment_ids = array_merge(
		$attachment_ids,
		array_map(
			function ( $result ) {
				return $result->meta_value;
			},
			$results
		)
	);

	$sql = '
SELECT post_content
FROM wp_posts
WHERE ID IN(' . generate_list_placeholders($post_ids, 1) . ')
AND post_content REGEXP \'((wp-image-|wp-att-)[0-9][0-9]*)|gallery_block_style|wp\:planet4\-blocks|href=|src=\'';

	$prepared_sql = $wpdb->prepare( $sql, $post_ids ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
	$results      = $wpdb->get_results( $prepared_sql ); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

	foreach ( (array) $results as $text ) {
		$text = $text->post_content;
		$attachment_ids = array_merge( $attachment_ids, get_attachments_used_in_content( $text ) );
	}

	$attachment_ids = array_unique( $attachment_ids );
	sort( $attachment_ids );

	// The post ids are reorderd as sort all attachment ids first and then append the post id to array.(Added for simplification of import process).
	$attachment_ids = array_diff( $attachment_ids, $post_ids );
	$post_ids       = array_merge( $attachment_ids, $post_ids );

	return $post_ids;
}
