<?php
/**
 * P4 Campaigns
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_Campaigns' ) ) {
	/**
	 * Class P4_Campaigns
	 */
	class P4_Campaigns {

		/**
		 * Taxonomy
		 *
		 * @var string $taxonomy
		 */
		private $taxonomy = 'post_tag';
		/**
		 * Page Types
		 *
		 * @var array $page_types
		 */
		public $page_types = [];
		/**
		 * Localizations
		 *
		 * @var array $localizations
		 */
		public $localizations = [];

		/**
		 * Taxonomy_Image constructor.
		 */
		public function __construct() {
			$this->localizations = [
				'media_title' => esc_html__( 'Select Image', 'planet4-master-theme-backend' ),
			];
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'post_tag_add_form_fields', [ $this, 'add_taxonomy_form_fields' ] );
			add_action( 'post_tag_edit_form_fields', [ $this, 'add_taxonomy_form_fields' ] );
			add_action( 'create_post_tag', [ $this, 'save_taxonomy_meta' ] );
			add_action( 'edit_post_tag', [ $this, 'save_taxonomy_meta' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

			add_filter( 'manage_edit-post_tag_columns', [ $this, 'edit_taxonomy_columns' ] );
			add_filter( 'manage_post_tag_custom_column', [ $this, 'manage_taxonomy_custom_column' ], 10, 3 );
			add_filter( 'manage_edit-post_tag_sortable_columns', [ $this, 'manage_taxonomy_custom_sortable_column' ], 10, 3 );
		}

		/**
		 * Add custom field(s) to taxonomy form.
		 *
		 * @param WP_Term $wp_tag The object passed to the callback when on Edit Tag page.
		 */
		public function add_taxonomy_form_fields( $wp_tag ) {
			$this->page_types = get_terms(
				[
					'hide_empty' => false,
					'orderby'    => 'name',
					'taxonomy'   => 'p4-page-type',
				]
			);

			if ( isset( $wp_tag ) && $wp_tag instanceof WP_Term ) {
				$selected_page_types = get_term_meta( $wp_tag->term_id, 'selected_page_types' );
				if ( ! isset( $selected_page_types[0] ) ) {
					$selected_page_types[0] = [];
				}

				$attachment_id    = get_term_meta( $wp_tag->term_id, 'tag_attachment_id', true );
				$image_attributes = wp_get_attachment_image_src( $attachment_id, 'full' );
				$attachment_url   = $image_attributes ? $image_attributes[0] : '';

				$happypoint_attachment_id    = get_term_meta( $wp_tag->term_id, 'happypoint_attachment_id', true );
				$happypoint_image_attributes = wp_get_attachment_image_src( $happypoint_attachment_id, 'full' );
				$happypoint_attachment_url   = $happypoint_image_attributes ? $happypoint_image_attributes[0] : '';

				$happypoint_bg_opacity = get_term_meta( $wp_tag->term_id, 'happypoint_bg_opacity', true );
				$happypoint_bg_opacity = $happypoint_bg_opacity ?? '30';

				$redirect_page = get_term_meta( $wp_tag->term_id, 'redirect_page', true );
				$dropdown_args = [
					'show_option_none' => __( 'Select Page', 'planet4-master-theme-backend' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'selected'         => $redirect_page,
					'name'             => 'redirect_page',
				];
				?>

				<tr class="form-field edit-wrap">
					<th>
						<label><?php echo __( 'Redirect Page', 'planet4-master-theme-backend' ); ?></label>
					</th>
					<td>
						<?php wp_dropdown_pages( $dropdown_args ); ?>
						<p class="description"><?php echo __( 'Leave this empty if you want to use the automated Tag page. Otherwise pick a page to redirect this Tag to.', 'planet4-master-theme-backend' ); ?></p>
					</td>
				</tr>
				<tr>
					<th colspan="2">
						<?php esc_html_e( 'Column block: Choose which Page Types will populate the content of the Column block. If no box is checked Publications will appear by default.', 'planet4-master-theme-backend' ); ?>
					</th>
				</tr>
				<?php foreach ( $this->page_types as $index => $page_type ) { ?>
					<tr class="form-field edit-wrap term-page-type-<?php echo esc_attr( $page_type->slug ); ?>-wrap">
						<th></th>
						<td>
							<div class="field-block shortcode-ui-field-checkbox shortcode-ui-attribute-p4_page_type_<?php echo esc_attr( $page_type->slug ); ?>">
								<label for="shortcode-ui-p4_page_type_<?php echo esc_attr( $page_type->slug ); ?>">
									<input type="checkbox" name="p4_page_type[]" id="shortcode-ui-p4_page_type_<?php echo esc_attr( $page_type->slug ); ?>" value="<?php echo esc_attr( $page_type->slug ); ?>" <?php echo in_array( $page_type->slug, $selected_page_types[0], true ) ? 'checked' : ''; ?>>
									<?php echo esc_html( $page_type->name ); ?>

								</label>
							</div>
						</td>
					</tr>
				<?php } ?>
				<tr class="form-field edit-wrap term-image-wrap">
					<th>
						<label><?php esc_html_e( 'Image', 'planet4-master-theme-backend' ); ?></label>
					</th>
					<td>
						<input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag-attachment-id field-id" value="<?php echo esc_attr( $attachment_id ); ?>" />
						<input type="hidden" name="tag_attachment" id="tag_attachment" class="tag-attachment-url field-url" value="<?php echo esc_url( $attachment_url ); ?>" />
						<button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
							<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme-backend' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'Associate this tag with an image.', 'planet4-master-theme-backend' ); ?></p>
						<img class="attachment-thumbnail size-thumbnail" src="<?php echo esc_url( $attachment_url ); ?>"/>
						<i class="dashicons dashicons-dismiss <?php echo $image_attributes ? '' : 'hidden'; ?>" style="cursor: pointer;"></i>
					</td>
				</tr>
				<tr class="form-field edit-wrap term-happypoint-wrap">
					<th>
						<label><?php esc_html_e( 'Image Subscribe', 'planet4-master-theme-backend' ); ?></label>
					</th>
					<td>
						<input type="hidden" name="happypoint_attachment_id" id="happypoint_attachment_id" class="happypoint-attachment-id field-id" value="<?php echo esc_attr( $happypoint_attachment_id ); ?>" />
						<input type="hidden" name="happypoint_attachment" id="happypoint_attachment" class="happypoint-attachment-url field-url" value="<?php echo esc_url( $happypoint_attachment_url ); ?>" />
						<button class="button insert-media add_media" name="insert_happypoint_image_button" id="insert_happypoint_image_button" type="button">
							<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme-backend' ); ?>
						</button>
						<p class="description"><?php esc_html_e( 'Choose a background image for the Subscribe block.', 'planet4-master-theme-backend' ); ?></p>
						<img class="attachment-thumbnail size-thumbnail" src="<?php echo esc_url( $happypoint_attachment_url ); ?>"/>
						<i class="dashicons dashicons-dismiss <?php echo $happypoint_image_attributes ? '' : 'hidden'; ?>" style="cursor: pointer;"></i>
					</td>
				</tr>
				<tr class="form-field edit-wrap term-happypoint-opacity-wrap">
					<th>
						<label><?php echo __( 'Happy Point Opacity', 'planet4-master-theme-backend' ); ?></label>
					</th>
					<td>
						<input type="number" name="happypoint_bg_opacity" id="happypoint_bg_opacity" class="happypoint-opacity-id field-id" value="<?php echo esc_attr( $happypoint_bg_opacity ); ?>" min="1" max="100"/>
						<p class="description"><?php echo __( 'We use an overlay to fade the image back. Use a number between 1 and 100, the higher the number, the more faded the image will look. If you leave this empty, the default of 30 will be used.', 'planet4-master-theme-backend' ); ?></p>
					</td>
				</tr>
				<?php
			} else {
				$dropdown_args = [
					'show_option_none' => __( 'Select Page', 'planet4-master-theme-backend' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'name'             => 'redirect_page',
				];
				?>
				<div class="form-field add-wrap">
					<label><?php echo __( 'Redirect Page', 'planet4-master-theme-backend' ); ?></label>
					<?php wp_dropdown_pages( $dropdown_args ); ?>
					<p class="description"><?php echo __( 'Leave this empty if you want to use the automated Tag page. Otherwise pick a page to redirect this Tag to.', 'planet4-master-theme-backend' ); ?></p>
				</div>
				<div class="form-field add-wrap term-image-wrap">
					<label><?php esc_html_e( 'Image', 'planet4-master-theme-backend' ); ?></label>
					<input type="hidden" name="tag_attachment_id" id="tag_attachment_id" class="tag_attachment_id field-id" value="" />
					<input type="hidden" name="tag_attachment" id="tag_attachment" class="tag-attachment-url field-url" value="" />
					<button class="button insert-media add_media" name="insert_image_tag_button" id="insert_image_tag_button" type="button">
						<?php esc_html_e( 'Select/Upload Image', 'planet4-master-theme-backend' ); ?>
					</button>
					<p class="description"><?php esc_html_e( 'Associate this tag with an image.', 'planet4-master-theme-backend' ); ?></p>
					<img class="attachment-thumbnail size-thumbnail" src="" />
					<i class="dashicons dashicons-dismiss hidden" style="cursor: pointer;"></i>
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
			// Save the selected page types for this campaign.
			$selected_page_types = $_POST['p4_page_type'] ?? [];
			if ( $this->validate_page_types( $selected_page_types ) ) {
				update_term_meta( $term_id, 'selected_page_types', $selected_page_types );
			}

			$field_id       = 'tag_attachment_id';
			$field_url      = 'tag_attachment';
			$attachment_id  = filter_input( INPUT_POST, $field_id, FILTER_VALIDATE_INT );
			$attachment_url = filter_input( INPUT_POST, $field_url, FILTER_VALIDATE_URL );

			if ( $this->validate( $attachment_id ) ) {
				update_term_meta( $term_id, $field_id, $attachment_id );
				update_term_meta( $term_id, $field_url, $attachment_url );
			}

			$field_id       = 'happypoint_attachment_id';
			$field_url      = 'happypoint_attachment';
			$attachment_id  = filter_input( INPUT_POST, $field_id, FILTER_VALIDATE_INT );
			$attachment_url = filter_input( INPUT_POST, $field_url, FILTER_VALIDATE_URL );

			if ( $this->validate( $attachment_id ) ) {
				update_term_meta( $term_id, $field_id, $attachment_id );
				update_term_meta( $term_id, $field_url, $attachment_url );
			}

			$field_id              = 'happypoint_bg_opacity';
			$happypoint_bg_opacity = filter_input( INPUT_POST, $field_id, FILTER_VALIDATE_INT );

			if ( $this->validate( $happypoint_bg_opacity ) ) {
				update_term_meta( $term_id, $field_id, $happypoint_bg_opacity );
			}

			$redirect_page = $_POST['redirect_page'] ?? 0;
			if ( ! is_null( get_post( $redirect_page ) ) || ! $redirect_page ) {
				update_term_meta( $term_id, 'redirect_page', $redirect_page );
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
			$columns['image'] = __( 'Image', 'planet4-master-theme-backend' );
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
			if ( 'image' === $column ) {
				$attachment_id = get_term_meta( $term_id, 'tag_attachment_id', true );
				$output        = wp_get_attachment_image( $attachment_id );
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
			$columns['image'] = 'image';
			return $columns;
		}

		/**
		 * Validates the input.
		 *
		 * @param int $id The attachment id to be validated.
		 *
		 * @return bool True if validation is ok, false if validation fails.
		 */
		public function validate( $id ) : bool {
			if ( $id < 0 ) {
				return false;
			}
			return true;
		}

		/**
		 * Validates the page types input.
		 *
		 * @param array $selected_page_types The selected page types selected by the editor.
		 *
		 * @return bool True if validation is ok, false if validation fails.
		 */
		public function validate_page_types( $selected_page_types ) : bool {
			$page_types_slugs = [];
			$this->page_types = get_terms(
				[
					'hide_empty' => false,
					'orderby'    => 'name',
					'taxonomy'   => 'p4-page-type',
				]
			);

			if ( $this->page_types ) {
				foreach ( $this->page_types as $page_type ) {
					if ( $page_type instanceof WP_Term ) {
						$page_types_slugs[] = $page_type->slug;
					}
				}
			}

			if ( isset( $selected_page_types ) && is_array( $selected_page_types ) ) {
				foreach ( $selected_page_types as $selected_page_type ) {
					if ( ! in_array( $selected_page_type, $page_types_slugs, true ) ) {
						return false;
					}
				}
			}
			return true;
		}

		/**
		 * Load assets.
		 */
		public function enqueue_admin_assets() {
			if ( ! is_admin() || strpos( get_current_screen()->taxonomy, $this->taxonomy ) === false ) {
				return;
			}
			wp_register_script( $this->taxonomy, get_template_directory_uri() . "/admin/js/$this->taxonomy.js", [ 'jquery' ], null, true );
			wp_localize_script( $this->taxonomy, 'localizations', $this->localizations );
			wp_enqueue_script( $this->taxonomy );
			wp_enqueue_media();
		}
	}
}
