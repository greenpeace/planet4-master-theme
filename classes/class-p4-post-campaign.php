<?php
/**
 * P4 Post Campaign Template Settings
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_Post_Campaign' ) ) {
	/**
	 * Class P4_Post_Campaign
	 */
	class P4_Post_Campaign {

		/**
		 * Post Type
		 */
		const POST_TYPE = 'campaign';


		/**
		 * Taxonomy_Image constructor.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'init', [ $this, 'register_campaigns_cpt' ] );
			add_action( 'cmb2_admin_init', [ $this, 'register_campaigns_metaboxes' ] );
			add_action( 'add_meta_boxes', [ $this, 'campaign_page_templates_meta_box' ] );
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
			add_action( 'save_post_campaign', [ $this, 'save_campaign_page_templates_meta_box_data' ] );
		}

		/**
		 * Return a list of the available campaign themes
		 */
		public function campaign_themes() {
			$campaign_theme = [
				'antarctic' => __( 'Antarctic', 'planet4-master-theme-backend' ),
				'arctic'    => __( 'Arctic', 'planet4-master-theme-backend' ),
				'forest'    => __( 'Forest', 'planet4-master-theme-backend' ),
				'oceans'    => __( 'Oceans', 'planet4-master-theme-backend' ),
				'oil'       => __( 'Oil', 'planet4-master-theme-backend' ),
				'plastic'   => __( 'Plastics', 'planet4-master-theme-backend' ),
			];
			return $campaign_theme;
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
				'not_found_in_trash' => __( 'No campaigns found in Trash.', 'planet4-master-theme-backend' ),
			);

			$args = array(
				'labels'             => $labels,
				'description'        => __( 'Campaigns', 'planet4-master-theme-backend' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => false,
				'query_var'          => true,
				'rewrite'            => [ 'slug' => 'campaign' ],
				'capability_type'    => 'post',
				'has_archive'        => true,
				'taxonomies'         => [ 'category', 'post_tag' ],
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'          => 'dashicons-megaphone',
				'supports'           => [ 'title', 'editor', 'author', 'thumbnail', 'excerpt' ],
			);

			register_post_type( self::POST_TYPE, $args );
		}

		/**
		 * Add metabox for campaign page template selection on campaigns cpt
		 */
		public function campaign_page_templates_meta_box() {
			add_meta_box(
				'campaigns-page-templates',
				__( 'Campaign Templates', 'planet4-master-theme-backend' ),
				array( $this, 'campaign_page_templates_meta_box_callback' ),
				'campaign',
				'side'
			);
		}

		/**
		 * Callback function for campaign page template selection
		 *
		 * @param object $post The post object.
		 */
		public function campaign_page_templates_meta_box_callback( $post ) {

			// Add a nonce field so we can check for it later.
			wp_nonce_field( 'campaign_page_template_nonce_' . $post->ID, 'campaign_page_template_nonce' );

			$value = get_post_meta( $post->ID, '_campaign_page_template', true );

			$campaign_templates = $this->campaign_themes();
			?>
			<select id="campaign_page_template" name="campaign_page_template">
				<option value=""><?php _e( 'Default Template', 'planet4-master-theme-backend' ); ?>
				</option>
				<?php
				foreach ( $campaign_templates as $campaign => $campaign_template ) {
					?>
					<option value="<?php echo $campaign; ?>" <?php selected( $value, $campaign ); ?>>
					<?php echo $campaign_template; ?>
					</option>
					<?php
				}
				?>
			</select>
			<?php
		}

		/**
		 * Save campaigns page template data
		 *
		 * @param number $post_id The post id.
		 */
		public function save_campaign_page_templates_meta_box_data( $post_id ) {

			// Check if our nonce is set.
			if ( ! isset( $_POST['campaign_page_template_nonce'] ) ) {
				return;
			}

			// Verify that the nonce is valid.
			if ( ! wp_verify_nonce( $_POST['campaign_page_template_nonce'], 'campaign_page_template_nonce_' . $post_id ) ) {
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

			// Validate user input.
			if ( in_array( $_POST['campaign_page_template'], array_keys( $this->campaign_themes() ) ) ) {
				$campaign_page_template = $_POST['campaign_page_template'];
			}

			// Update the meta field in the database.
			update_post_meta( $post_id, '_campaign_page_template', $campaign_page_template );
		}

		/**
		 * Register Color Picker Metabox for navigation
		 */
		public function register_campaigns_metaboxes() {
			$prefix = 'sc_ch_';
			$themes = $this->campaign_themes();
			// Add default Greenpeace logo to array.
			$themes['greenpeace'] = __( 'Greenpeace', 'planet4-master-theme-backend' );

			$header_palette = [
				'#E5E5E5',
				'#32CA89',
				'#1BB6D6',
				'#22938D',
				'#186A70',
				'#043029',
				'#093944',
				'#042233',
				'#1A1A1A',
			];

			$primary_palette = [
				'#ffd204',
				'#6ed961',
				'#21cbca',
				'#ee562d',
				'#7a1805',
				'#2077bf',
			];

			$secondary_palette = [
				'#042233',
				'#093944',
				'#074365',
			];

			$cmb = new_cmb2_box(
				[
					'id'           => 'campaign_nav_settings_mb',
					'title'        => __( 'Page Design', 'planet4-master-theme-backend' ),
					'object_types' => [
						'campaign',
					],
					'context'      => 'normal',
					'priority'     => 'high',
					'show_names'   => true, // Show field names on the left.
				]
			);

			$cmb->add_field(
				[
					'name'    => __( 'Navigation', 'planet4-master-theme-backend' ),
					'id'      => 'campaign_nav_type',
					'type'    => 'radio_inline',
					'options' => [
						'planet4' => __( 'Planet 4 Navigation', 'planet4-master-theme-backend' ),
						'minimal' => __( 'Minimal Navigation', 'planet4-master-theme-backend' ),
					],
					'default' => 'planet4',
				]
			);

			$cmb->add_field(
				[
					'name'       => __( 'Navigation Color', 'planet4-master-theme-backend' ),
					'id'         => 'campaign_nav_color',
					'type'       => 'colorpicker',
					'classes'    => 'palette-only',
					'attributes' => [
						'data-colorpicker' => json_encode(
							[
								'palettes' => $header_palette,
							]
						),
					],
				]
			);

			$cmb->add_field(
				[
					'name'       => __( 'Header Color', 'planet4-master-theme-backend' ),
					'id'         => 'campaign_header_color',
					'type'       => 'colorpicker',
					'classes'    => 'palette-only',
					'attributes' => [
						'data-colorpicker' => json_encode(
							[
								'palettes' => $header_palette,
							]
						),
					],
				]
			);

			$cmb->add_field(
				[
					'name'             => 'Header Primary Font',
					'desc'             => 'Select an option',
					'id'               => 'campaign_header_primary',
					'type'             => 'select',
					'show_option_none' => '-----',
					'options'          => [
						'Anton'            => __( 'Anton', 'planet4-master-theme-backend' ),
						'Montserrat'       => __( 'Montserrat Bold', 'planet4-master-theme-backend' ),
						'Montserrat_Light' => __( 'Montserrat Light', 'planet4-master-theme-backend' ),
						'Sanctuary'        => __( 'Sanctuary', 'planet4-master-theme-backend' ),
						'Kanit'            => __( 'Kanit Extra Bold', 'planet4-master-theme-backend' ),
						'Save the Arctic'  => __( 'Save the Arctic', 'planet4-master-theme-backend' ),
					],
				]
			);

			$cmb->add_field(
				[
					'name'             => 'Header Secondary Font',
					'desc'             => 'Select an option',
					'id'               => 'campaign_header_secondary',
					'type'             => 'select',
					'show_option_none' => '-----',
					'options'          => [
						'monsterrat_semi'   => __( 'Montserrat Semi Bold', 'planet4-master-theme-backend' ),
						'kanit_semi'        => __( 'Kanit Semi Bold', 'planet4-master-theme-backend' ),
						'open_sans'         => __( 'Open Sans', 'planet4-master-theme-backend' ),
						'open_sans_shadows' => __( 'Open Sans Shadows', 'planet4-master-theme-backend' ),
					],
				]
			);

			$cmb->add_field(
				[
					'name'    => __( 'Body Font', 'planet4-master-theme-backend' ),
					'id'      => 'campaign_body_font',
					'type'    => 'radio_inline',
					'options' => [
						'lora'   => __( 'Serif', 'planet4-master-theme-backend' ),
						'roboto' => __( 'Sans Serif', 'planet4-master-theme-backend' ),
					],
					'default' => 'lora',
				]
			);

			$cmb->add_field(
				[
					'name'       => __( 'Primary Button Color', 'planet4-master-theme-backend' ),
					'id'         => 'campaign_primary_color',
					'type'       => 'colorpicker',
					'classes'    => 'palette-only',
					'attributes' => [
						'data-colorpicker' => json_encode(
							[
								'palettes' => $primary_palette,
							]
						),
					],
				]
			);

			$cmb->add_field(
				[
					'name'       => __( 'Secondary Button Color', 'planet4-master-theme-backend' ),
					'id'         => 'campaign_secondary_color',
					'type'       => 'colorpicker',
					'classes'    => 'palette-only',
					'attributes' => [
						'data-colorpicker' => json_encode(
							[
								'palettes' => $secondary_palette,
							]
						),
					],
				]
			);

			$cmb->add_field(
				[
					'name'    => 'Logo',
					'desc'    => 'Change the campaign logo',
					'id'      => 'campaign_logo',
					'type'    => 'select',
					'default' => 'greenpeace',
					'options' => $themes,
				]
			);

			$cmb->add_field(
				[
					'name'    => 'Logo Color',
					'desc'    => 'Change the campaign logo color (if not default)',
					'id'      => 'campaign_logo_color',
					'type'    => 'radio_inline',
					'default' => 'light',
					'options' => [
						'light' => __( 'Light', 'planet4-master-theme-backend' ),
						'dark'  => __( 'Dark', 'planet4-master-theme-backend' ),
					],
				]
			);
		}

		/**
		 * Load assets.
		 */
		public function enqueue_admin_assets() {
			wp_register_style( 'cmb-style', get_template_directory_uri() . '/admin/css/campaign.css' );
			wp_enqueue_style( 'cmb-style' );
		}
	}
}
