<?php

if ( ! class_exists( 'P4_Campaigns' ) ) {
	/**
	 * Class P4_Campaigns
	 */
	class P4_Campaigns {

		/** @var string $taxonomy */
		private $taxonomy = 'post_tag';
		/** @var string $post_type */
		private $post_type = 'campaigns';
		/** @var array $page_types */
		public $page_types = [];
		/** @var array $localizations */
		public $localizations = [];

		/**
		 * Taxonomy_Image constructor.
		 */
		public function __construct() {
			$this->localizations = [
				'media_title'  => esc_html__( 'Select Image', 'planet4-master-theme-backend' ),
			];
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {

			add_action( 'init',                                  array( $this, 'register_campaigns_cpt' ) );
			add_action( 'cmb2_admin_init',                       array( $this, 'register_campaigns_metaboxes' ) );

			add_action( 'post_tag_add_form_fields',              array( $this, 'add_taxonomy_form_fields' ) );
			add_action( 'post_tag_edit_form_fields',             array( $this, 'add_taxonomy_form_fields' ) );
			add_action( 'create_post_tag',                       array( $this, 'save_taxonomy_meta' ) );
			add_action( 'edit_post_tag',                         array( $this, 'save_taxonomy_meta' ) );
			add_action( 'admin_enqueue_scripts',                 array( $this, 'enqueue_admin_assets' ) );

			add_filter( 'manage_edit-post_tag_columns',          array( $this, 'edit_taxonomy_columns' ) );
			add_filter( 'manage_post_tag_custom_column',         array( $this, 'manage_taxonomy_custom_column' ), 10, 3 );
			add_filter( 'manage_edit-post_tag_sortable_columns', array( $this, 'manage_taxonomy_custom_sortable_column' ), 10, 3 );
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

			$field_id = 'happypoint_bg_opacity';
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
			if ( ! is_admin() || strpos( get_current_screen()->post_type, $this->post_type ) === false ) {
				return;
			}
			wp_register_script( $this->post_type, get_template_directory_uri() . "/assets/js/$this->post_type.js", array( 'jquery' ), null, true );
			wp_localize_script( $this->post_type, 'localizations', $this->localizations );
			wp_register_style( 'cmb-style', get_template_directory_uri() . '/assets/css/campaign.css' );
			wp_enqueue_style( 'cmb-style' );
			wp_enqueue_script( $this->post_type );
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
		 * Register Color Picker Metabox for navigation
		 */
		public function register_campaigns_metaboxes() {
			$prefix         = 'sc_ch_';
			$header_palette = [
				'#ffffff', '#1a1a1a', '#333333',
				'#686867', '#979796', '#cacac9',
				'#e3e2e2', '#8cbf4f', '#a9cd7a',
				'#c7dda4'
			];

			$primary_palette = [
				'#093944', '#e9cbba', '#e88c74',
				'#9f1916', '#7a1a0f', '#3c120e',
				'#14233a', '#ec6d3d', '#e9582e',
				'#e9582e', '#fcd00a',
			];

			$secondary_palette = [
				'#3aa975', '#25784f', '#94d1d5',
				'#40bbd2', '#1bb5d5', '#0ca9d5',
				'#037899', '#2db4b3', '#5bbfca',
				'#23928c', '#196b71',
			];

			$cmb = new_cmb2_box( [
				'id'           => 'campaign_nav_settings_mb',
				'title'        => __( 'Page Design', 'planet4-master-theme-backend' ),
				'object_types' => [
					'campaigns',
				],
				'context'      => 'normal',
				'priority'     => 'high',
				'show_names'   => true, // Show field names on the left.
			] );

			$cmb->add_field( [
				'name'    => __( 'Navigation', 'planet4-master-theme-backend' ),
				'id'      => 'campaign_nav_type',
				'type'    => 'radio_inline',
				'default' => 'planet4',
				'options' => [
					'planet4' => __( 'Planet 4 Navigation', 'planet4-master-theme-backend' ),
					'minimal' => __( 'Minimal Navigation', 'planet4-master-theme-backend' ),
				],
			] );

			$cmb->add_field( [
				'name'       => __( 'Navigation Color', 'planet4-master-theme-backend' ),
				'id'         => 'campaign_nav_color',
				'type'       => 'colorpicker',
				'classes'    => 'palette-only',
				'attributes' => [
					'data-colorpicker' => json_encode( [
						'palettes' => $header_palette,
					] ),
				],
			] );

			$cmb->add_field( [
				'name'       => __( 'Header Color', 'planet4-master-theme-backend' ),
				'id'         => 'campaign_header_color',
				'type'       => 'colorpicker',
				'classes'    => 'palette-only',
				'attributes' => [
					'data-colorpicker' => json_encode( [
						'palettes' => $header_palette,
					] ),
				],
			] );

			$cmb->add_field( [
				'name'             => 'Header Primary Font',
				'desc'             => 'Select an option',
				'id'               => 'campaign_header_primary',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => [
					'anton'            => __( 'Anton', 'planet4-master-theme-backend' ),
					'montserrat_bold'  => __( 'Montserrat Bold', 'planet4-master-theme-backend' ),
					'montserrat_light' => __( 'Montserrat Light', 'planet4-master-theme-backend' ),
					'sanctuary'        => __( 'Sanctuary', 'planet4-master-theme-backend' ),
					'kanit'            => __( 'Kanit', 'planet4-master-theme-backend' ),
					'arctic'           => __( 'Save the Arctic', 'planet4-master-theme-backend' ),
				],
			] );

			$cmb->add_field( [
				'name'             => 'Header Secondary Font',
				'desc'             => 'Select an option',
				'id'               => 'campaign_header_secondary',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => [
					'open_sans'       => __( 'Open Sans', 'planet4-master-theme-backend' ),
					'montserrat_semi' => __( 'Montserrat SemiBold', 'planet4-master-theme-backend' ),
					'shadows'         => __( 'Shadows in Light', 'planet4-master-theme-backend' ),
					'kanit_semi'      => __( 'Kanit SemiBold', 'planet4-master-theme-backend' ),
				],
			] );

			$cmb->add_field( [
				'name'    => __( 'Body Font', 'planet4-master-theme-backend' ),
				'id'      => 'campaign_body_font',
				'type'    => 'radio_inline',
				'default' => 'roboto',
				'options' => [
					'lora'   => __( 'Serif (Lora)', 'planet4-master-theme-backend' ),
					'roboto' => __( 'Sans Serif (Roboto)', 'planet4-master-theme-backend' ),
				],
			] );

			$cmb->add_field( [
				'name'    => __( 'Logo Color', 'planet4-master-theme-backend' ),
				'id'      => 'campaign_logo_color',
				'type'    => 'radio_inline',
				'default' => 'light',
				'options' => [
					'dark' => __( 'Black', 'planet4-master-theme-backend' ),
					'light' => __( 'White', 'planet4-master-theme-backend' ),
				],
			] );


			$cmb->add_field( [
				'name'       => __( 'Primary Button Color', 'planet4-master-theme-backend' ),
				'id'         => 'campaign_primary_color',
				'type'       => 'colorpicker',
				'classes'    => 'palette-only',
				'attributes' => [
					'data-colorpicker' => json_encode( [
						'color'    => false,
						'palettes' => $primary_palette,
					] ),
				],
			] );

			$cmb->add_field( [
				'name'       => __( 'Secondary Button Color', 'planet4-master-theme-backend' ),
				'id'         => 'campaign_secondary_color',
				'type'       => 'colorpicker',
				'classes'    => 'palette-only',
				'attributes' => [
					'data-colorpicker' => json_encode( [
						'palettes' => $secondary_palette,
					] ),
				],
			] );

			$cmb->add_field( [
				'name'             => 'Logo',
				'desc'             => 'Select an option',
				'id'               => 'campaign_logo',
				'type'             => 'select',
				'show_option_none' => true,
				'options'          => [
					'arctic'    => __( 'Save the Arctic', 'planet4-master-theme-backend' ),
					'antarctic' => __( 'Antarctic', 'planet4-master-theme-backend' ),
					'forest'    => __( 'Forests', 'planet4-master-theme-backend' ),
					'plastic'   => __( 'Plastics', 'planet4-master-theme-backend' ),
					'oceans'    => __( 'Oceans', 'planet4-master-theme-backend' ),
					'oil'       => __( 'Oil', 'planet4-master-theme-backend' ),
				],
			] );
		}

		/**
		 * Add campaign page metabox
		 */
		public function add_campaign_page_meta_box() {
			add_meta_box( 'campaign-page-meta-box', 'Campaign', array(
				$this,
				'campaign_page_meta_box_markup',
			), "page", "side", "high", null );
		}

		/**
         * Campaign page metabox markup
         *
		 * @param $object
		 */
		public function campaign_page_meta_box_markup( $object ) {
			wp_nonce_field( basename( __FILE__ ), "campaign-page-meta-box-nonce" );
			?>
            <div>
                <label for="is-campaign-page"><?php _e( 'Campaign Page', 'planet4-master-theme-backend' ); ?>
					<?php $is_campaign_page = get_post_meta( $object->ID, "is_campaign_page", true ); ?>
                    &nbsp;&nbsp;<input type="checkbox" name="is-campaign-page" <?php checked( 'on', $is_campaign_page ); ?> />
                </label>
            </div>
			<?php
		}

		/**
         * Save campaign meta
         *
		 * @param $post_id
		 * @param $post
		 * @param $update
		 *
		 * @return mixed
		 */
		public function save_campaign_page_meta_box( $post_id, $post, $update ) {
			if ( ! isset( $_POST["campaign-page-meta-box-nonce"] ) || ! wp_verify_nonce( $_POST["campaign-page-meta-box-nonce"], basename( __FILE__ ) ) ) {
				return $post_id;
			}

			if ( ! current_user_can( "edit_post", $post_id ) ) {
				return $post_id;
			}

			if ( defined( "DOING_AUTOSAVE" ) && DOING_AUTOSAVE ) {
				return $post_id;
			}

			$slug = "page";
			if ( $slug != $post->post_type ) {
				return $post_id;
			}

			$is_campaign_page = false;
			if ( isset( $_POST['is-campaign-page'] ) ) {
				$is_campaign_page = $_POST['is-campaign-page'];
			}

			update_post_meta( $post_id, 'is_campaign_page', $is_campaign_page );
		}
	}
}
