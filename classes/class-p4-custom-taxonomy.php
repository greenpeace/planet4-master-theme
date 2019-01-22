<?php

if ( ! class_exists( 'P4_Custom_Taxonomy' ) ) {

	/**
	 * Class P4_Custom_Taxonomy
	 */
	class P4_Custom_Taxonomy {

		const TAXONOMY           = 'p4-page-type';
		const TAXONOMY_PARAMETER = 'p4_page_type';
		const TAXONOMY_SLUG      = 'page-type';

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

			// Rewrites the permalink to a post belonging to this taxonomy.
			add_filter( 'post_link',                          array( $this, 'filter_permalink' ), 10, 3 );

			// Rewrites the permalink to this taxonomy's page.
			add_filter( 'term_link',                          array( $this, 'filter_term_permalink' ), 10, 3 );
			add_filter( 'post_rewrite_rules',                 array( $this, 'replace_taxonomy_terms_in_rewrite_rules' ), 10, 1 );
			add_filter( 'root_rewrite_rules',                 array( $this, 'add_terms_rewrite_rules' ), 10, 1 );
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
			$tags[ self::TAXONOMY_PARAMETER ] = __( 'P4 page type (A p4 page type term.)', 'planet4-master-theme-backend' );

			return $tags;
		}

		/**
		 * Add a dropdown to choose planet4 post type.
		 *
		 * @param WP_Post $post The WordPress that will be filtered/edited.
		 */
		public function create_taxonomy_metabox_markup( WP_Post $post ) {
			$attached_type = get_the_terms( $post, self::TAXONOMY );
			$current_type  = ( is_array( $attached_type ) ) ? $attached_type[0]->term_id : - 1;
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
					<option <?php selected( $current_type, $term->term_id ); ?>
						value="<?php echo esc_attr( $term->term_id ); ?>">
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

			if ( strpos( $permalink, '%' . self::TAXONOMY_PARAMETER . '%' ) === false ) {
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

			return str_replace( '%' . self::TAXONOMY_PARAMETER . '%', $taxonomy_slug, $permalink );
		}

		/**
		 * Get taxonomy's terms.
		 *
		 * @return array|int|WP_Error
		 */
		public function get_terms() {
			// Get planet4 page type taxonomy terms.
			return get_terms(
				[
					'fields'     => 'all',
					'hide_empty' => false,
					'taxonomy'   => self::TAXONOMY,
				]
			);
		}

		/**
		 * Get all taxonomy's terms, despite if wpml plugin is activated.
		 *
		 * @return array|int|WP_Error
		 */
		public function get_all_terms() {
			// Get taxonomy terms if wpml plugin is installed and activated.
			if ( function_exists( 'icl_object_id' ) ) {
				return $this->get_multilingual_terms();
			}
			return $this->get_terms();
		}

		/**
		 * Get all taxonomy's terms (for all languages available) if wpml is enabled.
		 *
		 * @return WP_Term[]
		 */
		public function get_multilingual_terms() {

			$all_terms           = [];
			$current_lang        = apply_filters( 'wpml_current_language', null );
			$available_languages = apply_filters( 'wpml_active_languages', null, 'orderby=id&order=desc' );

			foreach ( $available_languages as $lang ) {
				do_action( 'wpml_switch_language', $lang['language_code'] );
				$terms = get_terms(
					[
						'fields'     => 'all',
						'hide_empty' => false,
						'taxonomy'   => self::TAXONOMY,
					]
				);
				if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
					$all_terms = array_merge( $all_terms, $terms );
				}
			}

			do_action( 'wpml_switch_language', $current_lang );

			return $all_terms;
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

			$args = [
				'hierarchical' => false,
				'labels'       => $p4_page_type,
				'rewrite'      => [
					'slug' => self::TAXONOMY_SLUG,
				],
				'show_ui'      => true,
				'query_var'    => true,
				'meta_box_cb'  => [ $this, 'create_taxonomy_metabox_markup' ],
			];

			register_taxonomy( self::TAXONOMY, [ self::TAXONOMY_PARAMETER, 'post' ], $args );
		}

		/**
		 * Add each taxonomy term as a rewrite rule.
		 */
		public function add_terms_as_rewrite_rules() {
			// Add a rewrite rule on top of the chain for each slug
			// of this taxonomy type (e.g.: "publication", "story", etc.).
			$terms_slugs = $this->get_terms_slugs();
			foreach ( $terms_slugs as $slug ) {
				add_rewrite_rule( $slug . '/?$', 'index.php?' . self::TAXONOMY . '=' . $slug, 'top' );
			}
		}

		/**
		 * Filter for term_link.
		 *
		 * @param string $link     The link value.
		 * @param string $term     The term passed to the filter (unused).
		 * @param string $taxonomy Taxonomy of the given link.
		 *
		 * @return string The filtered permalink for this taxonomy.
		 */
		public function filter_term_permalink( $link, $term, $taxonomy ) {
			if ( self::TAXONOMY !== $taxonomy ) {
				return $link;
			}

			return str_replace( self::TAXONOMY_SLUG . '/', '', $link );
		}

		/**
		 * Get the slugs for all terms in this taxonomy.
		 *
		 * @return array Flat array containing the slug for every term.
		 */
		private function get_terms_slugs() : array {
			// Get planet4 page type taxonomy terms.
			$terms = $this->get_all_terms();

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

				return $term_slugs;
			}

			return [];
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
			$term_slugs = $this->get_terms_slugs();

			if ( $term_slugs ) {
				$terms_slugs_regex = implode( '|', $term_slugs );

				$new_rules = [];
				foreach ( $rules as $match => $rule ) {
					$new_match               = str_replace( '%' . self::TAXONOMY_PARAMETER . '%', "($terms_slugs_regex)", $match );
					$new_rule                = str_replace( '%' . self::TAXONOMY_PARAMETER . '%', self::TAXONOMY . '=', $rule );
					$new_rules[ $new_match ] = $new_rule;
				}

				return $new_rules;
			}

			return $rules;
		}

		/**
		 * Add each taxonomy term as a root rewrite rule.
		 * Filter hook for root_rewrite_rules.
		 *
		 * @param array $rules  Root rewrite rules passed by WordPress.
		 *
		 * @return array        The filtered root rewrite rules.
		 */
		public function add_terms_rewrite_rules( $rules ) {
			// Add a rewrite rule for each slug of this taxonomy type (e.g.: "publication", "story", etc.)
			// for p4 page type pages.
			// e.g | story/?$ | index.php?p4-page-type=story | .
			$terms_slugs = $this->get_terms_slugs();

			if ( $terms_slugs ) {
				foreach ( $terms_slugs as $slug ) {
					$rules[ $slug . '/?$' ] = 'index.php?' . self::TAXONOMY . '=' . $slug;
				}
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
			if ( isset( $_POST[ self::TAXONOMY ] ) && 'post' === $post->post_type &&
				filter_var( $_POST[ self::TAXONOMY ], FILTER_VALIDATE_INT ) ) {

				$selected = get_term_by( 'id', intval( $_POST[ self::TAXONOMY ] ), self::TAXONOMY ); // Input var okay.
				if ( false !== $selected && ! is_wp_error( $selected ) ) {
					// Save post type.
					wp_set_post_terms( $post_id, [ $selected->term_id ], self::TAXONOMY );
				}
			}

			// Check if post type is POST.
			// Check if post has a p4 page type term assigned to it and if none if assigned, assign the default p4 page type term.
			if ( 'post' === $post->post_type ) {

				// Check if post has an assigned term to it.
				$terms = wp_get_object_terms( $post_id, self::TAXONOMY );
				if ( ! is_wp_error( $terms ) ) {

					$default_p4_pagetype = $this->get_default_p4_pagetype();

					// Assign default p4-pagetype, if no term is assigned to post.
					if ( empty( $terms ) ) {
						if ( $default_p4_pagetype instanceof \WP_Term ) {
							wp_set_post_terms( $post_id, [ $default_p4_pagetype->term_id ], self::TAXONOMY );
						}
					} elseif ( count( $terms ) > 1 && $terms[0] instanceof \WP_Term ) { // Assign the first term, if more than one terms are assigned.
						wp_set_post_terms( $post_id, [ $terms[0]->term_id ], self::TAXONOMY );
					}
				}
			}
		}
	}
}
