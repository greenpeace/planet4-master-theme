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
		/** @var string $post_type */
		private $post_type = 'campaigns';
		/** @var array $page_types */
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
			add_action( 'init', [ $this, 'register_campaigns_cpt' ] );

			add_action( 'post_tag_add_form_fields', [ $this, 'add_taxonomy_form_fields' ] );
			add_action( 'post_tag_edit_form_fields', [ $this, 'add_taxonomy_form_fields' ] );
			add_action( 'create_post_tag', [ $this, 'save_taxonomy_meta' ] );
			add_action( 'edit_post_tag', [ $this, 'save_taxonomy_meta' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

			add_filter( 'manage_edit-post_tag_columns', [ $this, 'edit_taxonomy_columns' ] );
			add_filter( 'manage_post_tag_custom_column', [ $this, 'manage_taxonomy_custom_column' ], 10, 3 );
			add_filter( 'manage_edit-post_tag_sortable_columns', [ $this, 'manage_taxonomy_custom_sortable_column' ], 10, 3 );

			add_action( 'add_meta_boxes', [ $this, 'campaign_page_templates_meta_box' ] );
			add_action( 'save_post', [ $this, 'save_campaign_page_templates_meta_box_data' ] );
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
				$happypoint_bg_opacity = $happypoint_bg_opacity ?? '30'; ?>

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
			<?php } else { ?>
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
			wp_register_script( $this->taxonomy, get_template_directory_uri() . "/assets/js/$this->taxonomy.js", [ 'jquery' ], null, true );
			wp_localize_script( $this->taxonomy, 'localizations', $this->localizations );
			wp_enqueue_script( $this->taxonomy );
			wp_enqueue_media();
		}

		/**
		 * Register campaigns cpt
		 */
		public function register_campaigns_cpt() {

			$labels = array(
				'name'               => _x( 'Campaigns', 'post type general name', 'planet4-master-theme-backend' ),
				'singular_name'      => _x( 'Campaign', 'post type singular name', 'planet4-master-theme-backend' ),
				'menu_name'          => _x( 'Campaigns', 'admin menu', 'planet4-master-theme-backend' ),
				'name_admin_bar'     => _x( 'Campaign', 'add new on admin bar', 'planet4-master-theme-backend' ),
				'add_new'            => _x( 'Add New', 'campaign', 'planet4-master-theme-backend' ),
				'add_new_item'       => __( 'Add New Campaign', 'planet4-master-theme-backend' ),
				'new_item'           => __( 'New Campaign', 'planet4-master-theme-backend' ),
				'edit_item'          => __( 'Edit Campaign', 'planet4-master-theme-backend' ),
				'view_item'          => __( 'View Campaign', 'planet4-master-theme-backend' ),
				'all_items'          => __( 'All Campaigns', 'planet4-master-theme-backend' ),
				'search_items'       => __( 'Search Campaigns', 'planet4-master-theme-backend' ),
				'parent_item_colon'  => __( 'Parent Campaigns:', 'planet4-master-theme-backend' ),
				'not_found'          => __( 'No campaigns found.', 'planet4-master-theme-backend' ),
				'not_found_in_trash' => __( 'No campaigns found in Trash.', 'planet4-master-theme-backend' )
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Campaigns', 'planet4-master-theme-backend' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => array( 'slug' => 'campaign' ),
				'capability_type'    => 'post',
				'has_archive'        => true,
				'taxonomies'         => array( 'category', 'post_tag' ),
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'          => 'dashicons-megaphone',
				'supports'           => array( 'title', 'editor', 'author', 'thumbnail', 'excerpt', 'comments' )
			);

			register_post_type( $this->post_type, $args );
		}

		/**
		 * Add metabox for campaign page template selection on campaigns cpt
		 */
		public function campaign_page_templates_meta_box() {
			add_meta_box(
				'campaigns-page-templates',
				__( 'Page Templates', 'sitepoint' ),
				array( $this, 'campaign_page_templates_meta_box_callback' ),
				'campaigns',
				'side'
			);
		}

		/**
		 * callback function for campaign page template selection
		 *
		 * @param $post
		 */
		public function campaign_page_templates_meta_box_callback( $post ) {

			// Add a nonce field so we can check for it later.
			wp_nonce_field( 'campaign_page_template_nonce', 'campaign_page_template_nonce' );

			$value = get_post_meta( $post->ID, '_campaign_page_template', true );

			$campaign_templates = array(
				'antarctic' => __( 'Antarctic', 'planet4-master-theme-backend' ),
				'arctic'    => __( 'Arctic', 'planet4-master-theme-backend' ),
				'forest'    => __( 'Forest', 'planet4-master-theme-backend' ),
				'oceans'    => __( 'Oceans', 'planet4-master-theme-backend' ),
				'oil'       => __( 'Oil', 'planet4-master-theme-backend' ),
				'plastic'   => __( 'Plastics', 'planet4-master-theme-backend' ),
			);

	?>
			<select id="campaign_page_template" name="campaign_page_template">
				<option value=""><?php _e( 'Select Campaign Template', 'planet4-master-theme-backend' ); ?></option>
				<?php foreach ( $campaign_templates as $campaign => $campaign_template ) {
					$selected = $value == $campaign ? 'selected="selected"' : '';
			?>
					<option
					value="<?php echo $campaign; ?>" <?php echo $selected; ?>><?php echo $campaign_template; ?></option>
					<?php
		}
		?>
			</select>
			<?php
		}

		/**
		 * Save campaigns page template data
		 *
		 * @param $post_id
		 */
		public function save_campaign_page_templates_meta_box_data( $post_id ) {

			// Check if our nonce is set.
			if ( ! isset( $_POST['campaign_page_template_nonce'] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['campaign_page_template_nonce'], 'campaign_page_template_nonce' ) ) {
				return;
			}

			// If this is an autosave, our form has not been submitted, so we don't want to do anything.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check the user's permissions.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			/* OK, it's safe for us to save the data now. */

			// Make sure that it is set.
			if ( ! isset( $_POST['campaign_page_template'] ) ) {
				return;
			}

			// Sanitize user input.
			$campaign_page_template = sanitize_text_field( $_POST['campaign_page_template'] );

			// Update the meta field in the database.
			update_post_meta( $post_id, '_campaign_page_template', $campaign_page_template );
		}
	}
}
