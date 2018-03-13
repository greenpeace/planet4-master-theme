<?php

if ( ! class_exists( 'P4_Taxonomy_Page' ) ) {
	/**
	 * Class P4MT_Taxonomy_Page
	 */
	class P4_Taxonomy_Page {

		/**
		 * Taxonomy_Page constructor.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'category_add_form_fields',              [ $this, 'add_taxonomy_form_fields' ] );
			add_action( 'category_edit_form_fields',             [ $this, 'add_taxonomy_form_fields' ] );
			add_action( 'edited_category',                       [ $this, 'save_taxonomy_meta' ] );
			add_action( 'create_category',                       [ $this, 'save_taxonomy_meta' ] );

			add_filter( 'manage_edit-category_columns',          [ $this, 'edit_taxonomy_columns' ] );
			add_filter( 'manage_category_custom_column',         [ $this, 'manage_taxonomy_custom_column' ], 10, 3 );
			add_filter( 'manage_edit-category_sortable_columns', [ $this, 'manage_taxonomy_custom_sortable_column' ], 10, 3 );
		}

		/**
		 * Add custom field(s) to taxonomy form.
		 *
		 * @param WP_Term $wp_tag The object passed to the callback when on Edit Tag page.
		 */
		public function add_taxonomy_form_fields( $wp_tag ) {

			$args = [
				'post_type'        => 'page',
				'show_option_none' => 'None',
				'name'             => 'category_page',
				'id'               => 'category-page-id',
				'class'            => 'category-page-class',
			];

			// Filter issue pages.
			$options          = get_option( 'planet4_options' );
			$explore_page_id  = $options['explore_page'];
			if ( $explore_page_id ) {
				$args['child_of'] = $explore_page_id;
			}

			if ( isset( $wp_tag ) && $wp_tag instanceof WP_Term ) {
				$category_page = get_term_meta( $wp_tag->term_id, 'category_page', true );
				if ( $category_page ) {
					$args['selected'] = $category_page;
				} ?>
				<tr class="form-field edit-wrap term-category-page-wrap">
					<th>
						<label><?php echo esc_html__( 'Select Category Page', 'planet4-master-theme' ); ?></label>
					</th>
					<td>
						<?php wp_dropdown_pages( array_map( 'esc_attr', $args ) ); ?>
						<p class="description"><?php echo esc_html__( 'Associate this category with a page.', 'planet4-master-theme' ); ?></p>
					</td>
				</tr>
			<?php } else { ?>
				<div class="form-field add-wrap term-category-page-wrap">
					<label><?php esc_html_e( 'Select Category Page', 'planet4-master-theme' ); ?></label>
					<?php wp_dropdown_pages( array_map( 'esc_attr', $args ) ); ?>
					<p class="description"><?php esc_html__( 'Associate this category with a page.', 'planet4-master-theme' ); ?></p>
				</div>
				<?php
			}
		}

		/**
		 * Save taxonomy custom field(s).
		 *
		 * @param int $term_id The ID of the WP_Term object that is added or edited.
		 */
		public function save_taxonomy_meta( $term_id ) {
			$field_id = 'category_page';
			$category_page = filter_input( INPUT_POST, $field_id, FILTER_VALIDATE_INT );

			if ( $this->validate( $category_page ) ) {
				update_term_meta( $term_id, $field_id, $category_page );
			}
		}

		/**
		 * Add custom column.
		 *
		 * @param array $columns Associative array with the columns of the taxonomy.
		 *
		 * @return array Associative array with the columns of the taxonomy.
		 */
		public function edit_taxonomy_columns( $columns ) : array {
			$columns['category_page'] = __( 'Category Page', 'planet4-master-theme' );
			return $columns;
		}

		/**
		 * Apply custom output to a custom column.
		 *
		 * @param string $output The html to be applied to each row of the $column.
		 * @param string $column The name of the column to be managed.
		 * @param int    $term_id The ID of the WP_Term object that is added or edited.
		 *
		 * @return string The new html to be applied to each row of the $column.
		 */
		public function manage_taxonomy_custom_column( $output, $column, $term_id ) : string {
			if ( 'category_page' === $column ) {
				$category_page = get_term_meta( $term_id, 'category_page', true );
				$output        = '<a href="' . get_edit_post_link( $category_page ) . '" target="_blank">' . get_the_title( $category_page ) . '</a>';
			}
			return $output;
		}

		/**
		 * Make column sortable.
		 *
		 * @param array $columns Associative array with the columns of the taxonomy.
		 *
		 * @return array Associative array with the columns of the taxonomy.
		 */
		public function manage_taxonomy_custom_sortable_column( $columns ) : array {
			$columns['category_page'] = 'category_page';
			return $columns;
		}

		/**
		 * Validates the input.
		 *
		 * @param int    $id The attachment id to be validated.
		 *
		 * @return bool True if validation is ok, false if validation fails.
		 */
		public function validate( $id ) : bool {
			if ( $id < 0 ) {
				return false;
			}
			return true;
		}
	}
}
