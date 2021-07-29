<?php
/**
 * Detect and remove duplicate postmeta command
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command;

/**
 * Class for detect and remove duplicate postmeta records.
 */
class Duplicated_Postmeta {

	/**
	 * List of meta_key check in duplicate list.
	 *
	 * @const array META_KEY_LIST.
	 */
	public const META_KEY_LIST = [
		'sm_cloud',
		'weight',
		'_credit_text',
		'_wp_attachment_image_alt',
		'_searchwp_last_index',
		'_media_restriction',
	];

	/**
	 * Remove duplicate postmeta records
	 *
	 * @return int
	 */
	public static function remove() {

		global $wpdb;

		// phpcs:disable
		$sql = "DELETE t1 FROM wp_postmeta t1 
				INNER JOIN wp_postmeta t2  
				WHERE  t1.meta_id < t2.meta_id 
				AND  t1.meta_key = t2.meta_key 
				AND t1.post_id = t2.post_id 
				AND t1.meta_key IN (" . self::generate_placeholders( self::META_KEY_LIST, 1 ) . ")";

		$prepared_sql = $wpdb->prepare( $sql, self::META_KEY_LIST );

		return $wpdb->query( $prepared_sql );
		// phpcs:enable
	}

	/**
	 * Detect duplicate postmeta records
	 *
	 * @return object
	 */
	public static function detect() {

		global $wpdb;

		// phpcs:disable
		$sql = "SELECT `meta_key`, COUNT(post_id) AS all_count , COUNT(DISTINCT post_id) AS unique_count
				FROM `wp_postmeta`
				GROUP by `meta_key`
				HAVING all_count <> unique_count
				ORDER BY `all_count` DESC";

		return $wpdb->get_results( $sql );
		// phpcs:enable
	}

	/**
	 * Generate a bunch of placeholders for use in an IN query.
	 *
	 * @param array $items The items to generate placeholders for.
	 * @param int   $start_index The start index to use for creating the placeholders.
	 * @return string The generated placeholders string.
	 */
	private static function generate_placeholders( array $items, int $start_index ): string {
		$placeholders = [];
		foreach ( range( $start_index, count( $items ) + $start_index - 1 ) as $i ) {
			$placeholders[] = "'%$i\$s'";
		}
		return implode( ',', $placeholders );
	}
}
