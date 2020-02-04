<?php
/**
 * @package P4GBKS\Rest
 */

namespace P4GBKS\Rest;

use WP_REST_Server;

/**
 * This class is just a place for add_endpoints to live.
 */
class Rest_Api {
	private const REST_NAMESPACE = 'planet4/v1';

	/**
	 * Add custom endpoints.
	 */
	public static function add_endpoints(): void {
		add_action(
			'rest_api_init',
			static function () {
				/**
				 * A lightweight endpoint to get all posts with only id and title.
				 */
				register_rest_route(
					self::REST_NAMESPACE,
					'/all-published-posts',
					[
						[
							'methods'  => WP_REST_Server::READABLE,
							'callback' => static function () {
								global $wpdb;

								if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
									// This is public data, no nonce needed.
									// phpcs:ignore WordPress.Security.NonceVerification.Recommended
									if ( ! isset( $_GET['post_language'] ) ) {
										return new \WP_REST_Response(
											'WPML is active so you need to query posts with the `post_language` query parameter.',
											400
										);
									}
									// This is public data, no nonce needed.
									// phpcs:ignore WordPress.Security.NonceVerification.Recommended
									$query = self::get_wpml_posts_query( $_GET['post_language'] );
								} else {
									$query = "SELECT id, post_title FROM wp_posts WHERE post_status = 'publish' AND post_type = 'post'";
								}
								// The query is prepared, just not in this line.
								// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
								$result = $wpdb->get_results( $query );

								return rest_ensure_response( $result );
							},
						],
					]
				);
			}
		);
	}

	/**
	 * Create a query for all posts with a certain language code.
	 *
	 * @param string $language_code Return posts with this language code.
	 * @return string The prepared query.
	 */
	private static function get_wpml_posts_query( string $language_code ): string {
		global $wpdb;

		return $wpdb->prepare(
			"SELECT p.id,p.post_title
				FROM wp_posts p
         		JOIN wp_icl_translations t
              		ON p.ID = t.element_id AND t.element_type = 'post_post'
				WHERE p.post_type = 'post'
  					AND p.post_status = 'publish'
  					AND t.language_code = %s
				ORDER BY p.post_date DESC; ",
			$language_code
		);
	}
}
