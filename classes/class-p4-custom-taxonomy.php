<?php

if ( ! class_exists( 'P4_Custom_Taxonomy' ) ) {

	/**
	 * Class P4_Custom_Taxonomy
	 */
	class P4_Custom_Taxonomy {

		const TAXONOMY = 'p4-page-type';

		/**
		 * P4_Custom_Taxonomy constructor.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Register actions for WordPress hooks and filters.
		 */
		private function hooks() {
			add_action( 'init',                               array( $this, 'register_taxonomy' ), 2 );
			add_action( 'created_term',                       array( $this, 'trigger_rewrite_rules' ), 10, 3 );
			add_action( 'edited_term',                        array( $this, 'trigger_rewrite_rules' ), 10, 3 );
			add_action( 'delete_term',                        array( $this, 'trigger_rewrite_rules' ), 10, 3 );
			add_action( 'save_post',                          array( $this, 'save_taxonomy_page_type' ) , 10, 2 );
			add_filter( 'available_permalink_structure_tags', array( $this, 'add_taxonomy_as_permalink_structure' ), 10, 1 );
			add_filter( 'post_link',                          array( $this, 'filter_permalink' ), 10, 3 );
			add_filter( 'post_rewrite_rules',                 array( $this, 'replace_taxonomy_terms_in_rewrite_rules' ), 10, 1 );
		}

		/**
		 * Add p4_page_type structure in available permalink tags for Settings -> Permalinks page.
		 * available_permalink_structure_tags filter.
		 *
		 * @param array $tags   Permalink tags that are displayed in Settings -> Permalinks.
		 *
		 * @return mixed
		 */
		public function add_taxonomy_as_permalink_structure( $tags ) {
			$tags['p4_page_type'] = __( 'P4 page type (A p4 page type term.)', 'planet4-master-theme-backend' );

			return $tags;
		}

		/**
		 * Add a dropdown to choose planet4 post type.
		 *
		 * @param WP_Post $post The WordPress that will be filtered/edited.
		 */
		public function create_taxonomy_metabox_markup( WP_Post $post ) {
			$attached_type = get_the_terms( $post, self::TAXONOMY );
			$current_type  = ( is_array( $attached_type ) ) ? $attached_type[0]->slug : - 1;
			$all_types = $this->get_terms();
			if ( -1 === $current_type ) {
				// Assign default p4-pagetype for new POST.
				$default_p4_pagetype = $this->get_default_p4_pagetype();
				$current_type = $default_p4_pagetype->slug;
			}

			wp_nonce_field( 'p4-save-page-type', 'p4-page-type-nonce' );
			?>
			<select name="<?php echo esc_attr( self::TAXONOMY ); ?>">
				<?php foreach ( $all_types as $term ) : ?>
					<option <?php selected( $current_type, $term->slug ); ?>
						value="<?php echo esc_attr( $term->slug ); ?>">
						<?php echo esc_html( $term->name ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<?php
		}

		/**
		 * Replace p4_page_type placeholder with the p4_page_type term for posts permalinks.
		 * Filter for post_link.
		 *
		 * @param string  $permalink The post's permalink.
		 * @param WP_Post $post      The post in question.
		 * @param bool    $leavename Whether to keep the post name.
		 *
		 * @return string   The filtered permalink.
		 */
		public function filter_permalink( $permalink, $post, $leavename ) {

			if ( strpos( $permalink, '%p4_page_type%' ) === false ) {
				return $permalink;
			}

			// Get post's taxonomy terms.
			$terms     = wp_get_object_terms( $post->ID, self::TAXONOMY );
			$all_terms = $this->get_terms();

			// Assign story slug if the taxonomy does not have any terms.
			$taxonomy_slug = 'story';
			if ( ! is_wp_error( $terms ) && ! empty( $terms ) && is_object( $terms[0] ) ) {
				$taxonomy_slug = $terms[0]->slug;
			} elseif ( ! is_wp_error( $terms ) && empty( $terms ) ) {
				if ( ! is_wp_error( $all_terms ) && ! empty( $all_terms ) && is_object( $all_terms[0] ) ) {
					$taxonomy_slug = $all_terms[0]->slug;
				}
			}

			return str_replace( '%p4_page_type%', $taxonomy_slug, $permalink );
		}

		/**
		 * Get taxonomy's terms.
		 *
		 * @return array|int|WP_Error
		 */
		public function get_terms() {
			// Get planet4 page type taxonomy terms.
			return get_terms( [
				'fields'     => 'all',
				'hide_empty' => false,
				'taxonomy'   => self::TAXONOMY,
			] );
		}

		/**
		 * Get default P4 pagetype.
		 *
		 * @return WP_term|int|WP_Error
		 */
		public function get_default_p4_pagetype() {
			$options             = get_option( 'planet4_options' );
			$default_p4_pagetype = $options['default_p4_pagetype'] ?? 0;

			if ( 0 === $default_p4_pagetype ) {
				// If default p4-pagetype setting not found, use taxonomy's first term.
				$all_terms           = $this->get_terms();
				$default_p4_pagetype = $all_terms[0] ?? 0;
			} else {
				$default_p4_pagetype = get_term( $default_p4_pagetype, self::TAXONOMY );
			}

			return $default_p4_pagetype;
		}

		/**
		 * Register a custom taxonomy for planet4 page types.
		 */
		public function register_taxonomy() {

			$p4_page_type = [
				'name'              => _x( 'Page Types', 'taxonomy general name', 'planet4-master-theme-backend' ),
				'singular_name'     => _x( 'Page Type', 'taxonomy singular name', 'planet4-master-theme-backend' ),
				'search_items'      => __( 'Search in Page Type', 'planet4-master-theme-backend' ),
				'all_items'         => __( 'All Page Types', 'planet4-master-theme-backend' ),
				'most_used_items'   => null,
				'parent_item'       => null,
				'parent_item_colon' => null,
				'edit_item'         => __( 'Edit Page Type', 'planet4-master-theme-backend' ),
				'update_item'       => __( 'Update Page Type', 'planet4-master-theme-backend' ),
				'add_new_item'      => __( 'Add new Page Type', 'planet4-master-theme-backend' ),
				'new_item_name'     => __( 'New Page Type', 'planet4-master-theme-backend' ),
				'menu_name'         => __( 'Page Types', 'planet4-master-theme-backend' ),
			];

			$args         = [
				'hierarchical' => false,
				'labels'       => $p4_page_type,
				'show_ui'      => true,
				'query_var'    => true,
				'rewrite'      => false,
				'meta_box_cb'  => [ $this, 'create_taxonomy_metabox_markup' ],
			];

			register_taxonomy( self::TAXONOMY, [ 'p4_page_type', 'post' ], $args );
		}

		/**
		 * Filter for post_rewrite_rules.
		 *
		 * @param array $rules   Post rewrite rules passed by WordPress.
		 *
		 * @return array        The filtered post rewrite rules.
		 */
		public function replace_taxonomy_terms_in_rewrite_rules( $rules ) {

			// Get planet4 page type taxonomy terms.
			$terms = $this->get_terms();

			if ( ! is_wp_error( $terms ) ) {

				$term_slugs = [];
				if ( ! empty( $terms ) ) {
					foreach ( $terms as $term ) {
						$term_slugs[] = $term->slug;
					}
				} elseif ( empty( $terms ) ) {
					// Add story slug also if the taxonomy does not have any terms.
					$term_slugs[] = 'story';
				}

				$terms_slugs = implode( '|', $term_slugs );

				$new_rules = [];
				foreach ( $rules as $match => $rule ) {
					$new_match               = str_replace( '%p4_page_type%', "($terms_slugs)", $match );
					$new_rule                = str_replace( '%p4_page_type%', 'p4_page_type=', $rule );
					$new_rules[ $new_match ] = $new_rule;
				}

				return $new_rules;
			}

			return $rules;
		}

		/**
		 * Regenerate and flush rewrite rules when a new p4_page_type is created/edited/deleted.
		 *
		 * @param int    $term_id  Term ID.
		 * @param int    $tt_id    Term taxonomy ID.
		 * @param string $taxonomy Taxonomy slug.
		 */
		public function trigger_rewrite_rules( $term_id, $tt_id, $taxonomy ) {
			if ( self::TAXONOMY !== $taxonomy ) {
				return;
			}
			flush_rewrite_rules();
		}

		/**
		 * Add first term of the taxonomy to the post if the post has not any taxonomy's terms assigned to it.
		 * Assign only the first term, if more than one terms are assigned to the post.
		 *
		 * @param int     $post_id Id of the saved post.
		 * @param WP_Post $post    Post object.
		 */
		public function save_taxonomy_page_type( $post_id, $post ) {

			// Ignore autosave.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check user's capabilities.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// Allow p4-page-type to be set from edit post and quick edit pages.
			// Make sure there's input.
			if ( isset( $_POST['p4-page-type'] ) && 'post' === $post->post_type ) { // Input var okay.
				$selected = get_term_by( 'slug', sanitize_text_field( wp_unslash( $_POST['p4-page-type'] ) ), 'p4-page-type' ); // Input var okay.
				if ( false !== $selected && ! is_wp_error( $selected ) ) {
					// Save post type.
					wp_set_post_terms( $post_id, sanitize_text_field( $selected->slug ), 'p4-page-type' );
				}
			}

			// Check if post type is POST.
			if ( 'post' === $post->post_type ) {

				// Check if post has an assigned term to it.
				$terms = wp_get_object_terms( $post_id, self::TAXONOMY );
				if ( ! is_wp_error( $terms ) ) {

					$default_p4_pagetype = $this->get_default_p4_pagetype();

					// Assign default p4-pagetype, if no term is assigned to post.
					if ( empty( $terms ) ) {
						if ( is_object( $default_p4_pagetype ) ) {
							wp_set_post_terms( $post_id, $default_p4_pagetype->slug, self::TAXONOMY );
						}
					} elseif ( count( $terms ) > 1 ) { // Assign the first term, if more than one terms are assigned.
						if ( ! is_wp_error( $terms ) && is_object( $terms[0] ) ) {
							wp_set_post_terms( $post_id, $terms[0]->slug, self::TAXONOMY );
						}
					}
				}
			}
		}
	}
}

