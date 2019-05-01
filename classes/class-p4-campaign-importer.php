<?php
/**
 * Campaign Data(Attachment) Importer
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_Campaign_Importer' ) ) {
	/**
	 * Class P4_Campaign_Importer.
	 */
	class P4_Campaign_Importer {
		/**
		 * AutoLoad Hooks
		 * */
		public function __construct() {
			add_action( 'wp_import_insert_post', [ $this, 'update_campaign_attachements' ], 10, 4 );
			add_filter( 'wp_import_post_terms', [ $this, 'filter_wp_import_post_terms' ], 10, 3 );
			add_action( 'import_end', [ $this, 'action_import_end' ], 10, 0 );
		}

		/**
		 * Filter the old attachement Ids from Campaign and replace them with the newly imported attachment Ids.
		 *
		 * @param integer $post_id Post ID.
		 * @param integer $original_post_id Original Post ID.
		 * @param array   $postdata Post data array.
		 * @param array   $post Post array.
		 */
		public function update_campaign_attachements( $post_id, $original_post_id, $postdata, $post ) {
			if ( 'campaign' === $post['post_type'] ) {
				$post_content = $post['post_content'];
				$old_ids      = [];
				$filter_term  = [];

				// Filter attachment ids from caption.
				preg_match_all( '#((wp-image-|wp-att-|attachment\_)(\d+))#', $post_content, $matches, PREG_SET_ORDER );
				foreach ( $matches as $match ) {
					$old_ids[]     = $match[3];
					$filter_term[] = $match[1];
				}

				// Filter attachment ids from shortcake code(shortcake_gallery, shortcake_happy_point, shortcake_media_video).
				preg_match_all( '#\[shortcake\_[a-zA-Z0-9\_\"\'\-\s\:\/\/\=\.\?\&]*\s((multiple_image|background|video_poster_img)[=][\"|\']([\d\s\,]*)[\"|\'])#', $post_content, $matches, PREG_SET_ORDER );
				foreach ( $matches as $match ) {
					if ( 'multiple_image' === $match[2] ) {
						$multiple_images = explode( ',', $match[3] );
						$old_ids         = array_merge( $old_ids, $multiple_images );
					} else {
						$old_ids[] = $match[3];
					}
					$filter_term[] = $match[1];
				}

				// Filter attachment ids from shortcake code(shortcake_carousel_header, shortcake_split_two_columns, shortcake_columns).
				preg_match_all( '#\s((image_[0-9]*|attachment_[0-9]*|issue_image|tag_image)[=][\"|\']([\d\s\,]*)[\"|\'])#', $post_content, $matches, PREG_SET_ORDER );
				foreach ( $matches as $match ) {
					$old_ids[]     = $match[3];
					$filter_term[] = $match[1];
				}

				// Filter attachment ids from [gallery] shortcode.
				preg_match_all( '#\[gallery\s+([ids=\"\']+([\d\s,]*)[\"\']).#', $post_content, $matches, PREG_SET_ORDER );
				foreach ( $matches as $match ) {
					foreach ( explode( ',', $match[2] ) as $id ) {
						$old_ids[] = (int) $id;
					}
					$filter_term[] = $match[1];
				}

				$old_ids = array_unique( $old_ids );
				sort( $old_ids );

				global $wpdb;

				// phpcs:disable
				$result = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%imported_attachment_id%'" );
				// phpcs:enable

				$attachment_mapping = [];

				foreach ( $result as $attachment_metadata ) {
					$new_attachment_id                        = $attachment_metadata->post_id;
					$attachment_data                          = maybe_unserialize( $attachment_metadata->meta_value );
					$old_attachment_id                        = $attachment_data['image_meta']['imported_attachment_id'];
					$attachment_mapping[ $old_attachment_id ] = $new_attachment_id;
				}

				$filter_data_array = [];
				foreach ( $filter_term as $filter_str ) {
					if ( strpos( $filter_str, 'multiple_image' ) !== false || strpos( $filter_str, 'ids' ) !== false ) {
						$multiple_images_ids = $filter_str;
						preg_match_all( '#(\d+)#', $multiple_images_ids, $matches, PREG_SET_ORDER );

						foreach ( $matches as $old_id ) {
							if ( isset( $attachment_mapping[ $old_id[0] ] ) ) {
								$multiple_images_ids = str_replace( $old_id, $attachment_mapping[ $old_id[0] ], $multiple_images_ids );
							}
						}
						$filter_data_array[] = [ $filter_str, $multiple_images_ids ];
					} else {
						foreach ( $attachment_mapping as $old_id => $new_id ) {
							$updated_str = str_replace( $old_id, $new_id, $filter_str );
							if ( $updated_str !== $filter_str ) {
								$filter_data_array[] = [ $filter_str, $updated_str ];
							}
						}
					}
				}

				foreach ( $filter_data_array as $filter_data ) {
					$post_content = str_replace( $filter_data[0], $filter_data[1], $post_content );
				}

				$updated_post = [
					'ID'           => $post_id,
					'post_title'   => $post['post_title'],
					'post_content' => $post_content,
				];
				wp_update_post( $updated_post );
			}
		}

		/**
		 * Update campaign attachement source ID in attachment metadata for future data mapping purpose.
		 *
		 * @param array   $post_terms Post term array.
		 * @param integer $post_id Post ID.
		 * @param object  $post Post object.
		 * @return array  $post_terms Post term array.
		 */
		public function filter_wp_import_post_terms( $post_terms, $post_id, $post ) {
			if ( 'attachment' === $post['post_type'] ) {
				$attachment_metadata = wp_get_attachment_metadata( $post_id );
				$attachment_metadata['image_meta']['imported_attachment_id'] = $post['post_id'];
				wp_update_attachment_metadata( $post_id, $attachment_metadata );
			}

			return $post_terms;
		}

		/**
		 * Clean the campaign attachment metadata.
		 */
		public function action_import_end() {
			global $wpdb;

			// phpcs:disable
			$result = $wpdb->get_results( "SELECT post_id, meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attachment_metadata' AND meta_value LIKE '%imported_attachment_id%'" );
			// phpcs:enable

			foreach ( $result as $attachment_metadata ) {
				$attachment_data = maybe_unserialize( $attachment_metadata->meta_value );
				unset( $attachment_data['image_meta']['imported_attachment_id'] );
				wp_update_attachment_metadata( $attachment_metadata->post_id, $attachment_data );
			}
		}
	}
}
