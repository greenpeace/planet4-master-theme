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
		 * Old and new attachment ids mapping var
		 *
		 * @var array $attachment_mapping
		 */
		private $attachment_mapping = [];

		/**
		 * AutoLoad Hooks
		 * */
		public function __construct() {
			add_action( 'wp_import_insert_post', [ $this, 'update_campaign_attachements' ], 10, 4 );
			add_filter( 'wp_import_post_terms', [ $this, 'filter_wp_import_post_terms' ], 10, 3 );
			add_filter( 'wp_import_post_data_processed', [ $this, 'set_imported_campaigns_as_drafts' ], 10, 2 );
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
				$filter_data  = [];

				$blocks = parse_blocks( $post_content );
				foreach ( $blocks as $block ) {
					// Fetch background image id from EN form block.
					if ( 'planet4-blocks/enform' === $block['blockName'] && isset( $block['attrs']['background'] ) ) {
						$filter_data[] = 'background":' . $block['attrs']['background'];
					}

					// Fetch HP background image id from HP block.
					if ( 'planet4-blocks/happypoint' === $block['blockName'] && isset( $block['attrs']['id'] ) ) {
						$filter_data[] = 'id":' . $block['attrs']['id'];
					}

					// Fetch video poster image id from Media block.
					if ( 'planet4-blocks/media-video' === $block['blockName'] && isset( $block['attrs']['video_poster_img'] ) ) {
						$filter_data[] = 'video_poster_img":' . $block['attrs']['video_poster_img'];
					}

					// Fetch image ids from Gallery block.
					if ( 'planet4-blocks/gallery' === $block['blockName'] && isset( $block['attrs']['multiple_image'] ) ) {
						$filter_data[] = 'multiple_image":"' . $block['attrs']['multiple_image'];
					}

					// Fetch image id from Carousel Header block.
					if ( 'planet4-blocks/carousel-header' === $block['blockName'] && isset( $block['attrs']['slides'] ) ) {
						foreach ( $block['attrs']['slides'] as $slide ) {
							$filter_data[] = 'image":' . $slide['image'];
						}
					}

					// Fetch issue/tag image id from Split Two Columns block.
					if ( 'planet4-blocks/split-two-columns' === $block['blockName'] && ( isset( $block['attrs']['issue_image'] ) || isset( $block['attrs']['tag_image'] ) ) ) {
						$filter_data[] = 'issue_image":' . $block['attrs']['issue_image'];
						$filter_data[] = 'tag_image":' . $block['attrs']['tag_image'];
					}

					// Fetch attachment ids from Columns block.
					if ( 'planet4-blocks/columns' === $block['blockName'] && isset( $block['attrs']['columns'] ) ) {
						foreach ( $block['attrs']['columns'] as $column ) {
							$filter_data[] = 'attachment":' . $column['attachment'];
						}
					}

					// Fetch image ids from Social media cards block.
					if ( 'planet4-blocks/social-media-cards' === $block['blockName'] && isset( $block['attrs']['cards'] ) ) {
						foreach ( $block['attrs']['cards'] as $card ) {
							$filter_data[] = 'image_id":' . $card['image_id'];
						}
					}

					// Fetch background_image from Take-action-boxout block.
					if ( 'planet4-blocks/take-action-boxout' === $block['blockName'] && isset( $block['attrs']['background_image'] ) ) {
						$filter_data[] = 'background_image":' . $block['attrs']['background_image'];
					}

					// Fetch native image block id.
					if ( 'core/image' === $block['blockName'] && isset( $block['attrs']['id'] ) ) {
						$filter_data[] = 'id":' . $block['attrs']['id'];
						$filter_data[] = 'wp-image-' . $block['attrs']['id'];
					}
				}

				// Check if attachement mapping var is empty and update it.
				if ( empty( $this->attachment_mapping ) ) {

					global $wpdb;

					// phpcs:disable
					$sql          = 'SELECT post_id, meta_value FROM %1$s WHERE meta_key = \'_wp_attachment_metadata\' AND meta_value LIKE \'%imported_attachment_id%\'';
					$prepared_sql = $wpdb->prepare( $sql, [ $wpdb->postmeta ] );
					$result       = $wpdb->get_results( $prepared_sql );
					// phpcs:enable

					foreach ( $result as $attachment_metadata ) {
						$new_attachment_id                              = $attachment_metadata->post_id;
						$attachment_data                                = maybe_unserialize( $attachment_metadata->meta_value );
						$old_attachment_id                              = $attachment_data['image_meta']['imported_attachment_id'];
						$this->attachment_mapping[ $old_attachment_id ] = $new_attachment_id;
					}
				}

				// Old ids and new ids(attachment ids) string data mapping.
				$filter_data_mapping = [];
				foreach ( $filter_data as $filter_str ) {
					if ( strpos( $filter_str, 'multiple_image' ) !== false || strpos( $filter_str, 'ids' ) !== false ) {
						$new_filter_str = $filter_str;
						preg_match_all( '#(\d+)#', $new_filter_str, $matches, PREG_SET_ORDER );

						foreach ( $matches as $old_id ) {
							if ( isset( $this->attachment_mapping[ $old_id[0] ] ) ) {
								$new_filter_str = str_replace( $old_id[0], $this->attachment_mapping[ $old_id[0] ], $new_filter_str );
							}
						}
						$filter_data_mapping[] = [ $filter_str, $new_filter_str ];
					} else {
						foreach ( $this->attachment_mapping as $old_id => $new_id ) {
							$updated_str = str_replace( $old_id, $new_id, $filter_str );
							if ( $updated_str !== $filter_str ) {
								$filter_data_mapping[] = [ $filter_str, $updated_str ];
							}
						}
					}
				}

				// Search replace filter data string(with old attachement ids) with updated attachment ids string.
				foreach ( $filter_data_mapping as $filter_data ) {
					$post_content = str_replace( $filter_data[0], $filter_data[1], $post_content );
				}

				// Update Page header fields background image in postmeta.
				$campaign_postmeta = [];
				if ( isset( $post['postmeta'] ) ) {
					foreach ( $post['postmeta'] as $metakey => $metadata ) {
						if ( 'background_image_id' === $metadata['key'] ) {
							if ( isset( $this->attachment_mapping[ $metadata['value'] ] ) ) {
								$post['postmeta'][ $metakey ]['value'] = $this->attachment_mapping[ $metadata['value'] ];
							}
						}
					}
					$campaign_postmeta = $post['postmeta'];
				}

				$updated_post = [
					'ID'           => $post_id,
					'post_title'   => $post['post_title'],
					'post_content' => $post_content,
					'postmeta'     => $campaign_postmeta,
				];
				wp_update_post( wp_slash( $updated_post ) );
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

				if ( ! empty( $this->attachment_mapping ) ) {
					$this->attachment_mapping = [];
				}
			}

			return $post_terms;
		}

		/**
		 * Clean the campaign attachment metadata.
		 */
		public function action_import_end() {
			global $wpdb;

			// phpcs:disable
			$sql          = 'SELECT post_id, meta_value FROM %1$s WHERE meta_key = \'_wp_attachment_metadata\' AND meta_value LIKE \'%imported_attachment_id%\'';
			$prepared_sql = $wpdb->prepare( $sql, [ $wpdb->postmeta ] );
			$result       = $wpdb->get_results( $prepared_sql );
			// phpcs:enable

			foreach ( $result as $attachment_metadata ) {
				$attachment_data = maybe_unserialize( $attachment_metadata->meta_value );
				unset( $attachment_data['image_meta']['imported_attachment_id'] );
				wp_update_attachment_metadata( $attachment_metadata->post_id, $attachment_data );
			}
		}

		/**
		 * Filter for wp_import_post_data_processed.
		 * Set imported campaign posts as drafts.
		 *
		 * @param array $postdata Post data that can be filtered.
		 * @param array $post     The post array to be inserted.
		 *
		 * @return array
		 */
		public function set_imported_campaigns_as_drafts( $postdata, $post ) {
			if ( 'campaign' === $post['post_type'] ) {
				$postdata['post_status'] = 'draft';
			}

			return $postdata;
		}
	}
}
