<?php
/**
 * @package P4GBKS\Rest
 */

namespace P4GBKS\Rest;

use WP_REST_Request;
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
		add_action( 'rest_api_init', [ __CLASS__, 'endpoints' ] );

	}

	/**
	 * Register custom rest API endpoints.
	 */
	public static function endpoints(): void {
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
							$query = "SELECT id, post_title FROM wp_posts WHERE post_status = 'publish' AND post_type = 'post' ORDER BY post_date DESC";
						}
						// The query is prepared, just not in this line.
						// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
						$result = $wpdb->get_results( $query );

						return rest_ensure_response( $result );
					},
				],
			]
		);
		/**
		 * Save meta to the preview of the current user.
		 */
		register_rest_route(
			self::REST_NAMESPACE,
			'/save-preview-meta',
			[
				[
					'methods'  => WP_REST_Server::CREATABLE,
					'callback' => static function ( $request ) {
						/**
						 * @var WP_REST_Request $request
						 */
						$post_id = $request['post_id'];

						$post = get_post( $post_id );

						if ( ! $post ) {
							return new \WP_REST_Response(
								'No such post exists.',
								400
							);
						}

						if ( ! current_user_can( 'edit_post', $post_id ) ) {
							return new \WP_REST_Response(
								'You do not have permission to edit this post.',
								403
							);

						}

						$old_autosave = wp_get_post_autosave( $post_id, get_current_user_id() );

						if ( ! $old_autosave ) {
							// No existing autosave, so let's create one. Should only happen once for each user.
							// @see \P4_Loader::do_not_delete_autosave The filter that ensures that.
							$revision_id = _wp_put_post_revision( $post, true );
						} else {
							$revision_id = $old_autosave->ID;
						}

						foreach ( $request['meta'] as $key => $value ) {
							update_metadata( 'post', $revision_id, $key, $value );
						}

						return rest_ensure_response( 'Saved all meta to the autosave revision.' );
					},
				],
			]
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
