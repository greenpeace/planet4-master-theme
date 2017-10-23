<?php

if ( ! class_exists( 'P4_Taxonomy_Image' ) ) {
	/**
	 * Class P4MT_Taxonomy_Image
	 */
	class P4_Taxonomy_Image {

		/**
		 * Taxonomy_Image constructor.
		 */
		public function __construct() {
			add_action( 'post_tag_add_form_fields', array( $this, 'tag_form_fields' ) );
			add_action( 'post_tag_edit_form_fields', array( $this, 'tag_form_fields' ) );
			add_action( 'create_post_tag', array( $this, 'save_tag_meta' ) );
			add_action( 'edit_post_tag', array( $this, 'save_tag_meta' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );

			add_filter( 'manage_edit-post_tag_columns', array( $this, 'edit_post_tag_columns' ) );
			add_filter( 'manage_post_tag_custom_column', array( $this, 'manage_post_tag_custom_column' ), 10, 3 );
			add_filter( 'manage_edit-post_tag_sortable_columns', array( $this, 'manage_post_tag_custom_sortable_column' ), 10, 3 );
		}

		/**
		 * Add/Edit Tag page custom field template.
		 *
		 * @param WP_Term $wp_tag The object passed to the callback when on Edit Tag page.
		 */
		public function tag_form_fields( $wp_tag ) {
			if ( isset( $wp_tag ) && $wp_tag instanceof WP_Term ) { ?>
				<tr class="form-field term-image-wrap">
					<th>
						<label for="tag_attachment_id"><?php esc_html_e( 'Image', 'planet4-master-theme' ); ?></label>
					</th>
					<td>
						<input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag_attachment_id" value="" />
						<button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
							<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'Associate this tag with an image.', 'planet4-master-theme' ); ?></p>
						<p class="tag_attachment">
							<?php echo wp_get_attachment_image( get_term_meta( $wp_tag->term_id, 'tag_attachment_id', true ) ); ?>
						</p>
					</td>
				</tr>
			<?php } else { ?>
				<div class="form-field term-image-wrap">
					<label for="tag_attachment_id"><?php esc_html_e( 'Image', 'planet4-master-theme' ); ?></label>
					<input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag_attachment_id" value="" />
					<button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
						<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Associate this tag with an image.', 'planet4-master-theme' ); ?></p>
					<p class="tag_attachment">
						<img class="attachment-thumbnail size-thumbnail" src=""/>
					</p>
				</div>
				<?php
			}
		}

		/**
		 * Save user custom input field.
		 *
		 * @param int $term_id The ID of the WP_Term object that is added or edited.
		 */
		public function save_tag_meta( $term_id ) {
			if ( isset( $_POST['tag_attachment_id'] ) ) {
				$attachment_id = $this->validate( $_POST['tag_attachment_id'] );
				if ( $attachment_id ) {
					update_term_meta( $term_id, 'tag_attachment_id', $attachment_id );
				}
			}
		}

		/**
		 * Add custom column.
		 *
		 * @param array $columns Associative array with the columns of the page.
		 *
		 * @return array
		 */
		public function edit_post_tag_columns( $columns ) : array {
			$columns['image'] = __( 'Image', 'planet4-master-theme' );

			return $columns;
		}

		/**
		 * Apply custom output to a custom column.
		 *
		 * @param string $output The html to be applied to each row of the $column.
		 * @param string $column The name of the column to be managed.
		 * @param int    $term_id The ID of the WP_Term object that is added or edited.
		 *
		 * @return string
		 */
		public function manage_post_tag_custom_column( $output, $column, $term_id ) : string {
			if ( 'image' === $column ) {
				$attachment_id = get_term_meta( $term_id, 'tag_attachment_id', true );
				$output        = wp_get_attachment_image( $attachment_id );
			}

			return $output;
		}

		/**
		 * Make column sortable.
		 *
		 * @param array $columns Associative array with the columns of the page.
		 *
		 * @return array
		 */
		public function manage_post_tag_custom_sortable_column( $columns ) : array {
			$columns['image'] = 'image';

			return $columns;
		}

		/**
		 * Validates the input.
		 *
		 * @param string $input The user input to be validated.
		 *
		 * @return mixed Int if validation is ok, false if validation fails.
		 */
		public function validate( $input ) {
			$input = (int) $input;
			if ( 0 >= $input ) {
				return false;
			}

			return $input;
		}

		/**
		 * Load assets.
		 *
		 * @param string $hook The slug name of the current admin page.
		 */
		public function enqueue_admin_assets( $hook ) {
			if ( ! is_admin() || strpos( get_current_screen()->taxonomy, 'post_tag' ) === false ) {
				return;
			}

			wp_enqueue_media();
		}
	}
}
