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
			add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
			add_action( 'cmb2_render_sidebar_link', [ $this, 'cmb2_render_sidebar_link_field_callback' ], 10, 5 );
			add_action( 'cmb2_render_footer_icon_link', [ $this, 'cmb2_render_footer_icon_link_field_callback' ], 10, 5 );
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
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => [ 'slug' => 'campaign' ],
				'capability_type'    => [ 'campaign', 'campaigns' ],
				'map_meta_cap'       => true,
				'has_archive'        => true,
				'hierarchical'       => false,
				'menu_position'      => null,
				'menu_icon'          => 'dashicons-megaphone',
				'show_in_rest'       => true,
				'supports'           => [
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'revisions',
					// Required to expose meta fields in the REST API.
					'custom-fields',
				],
			);

			register_post_type( self::POST_TYPE, $args );

			self::campaign_field( 'theme' );
			self::campaign_field( 'campaign_logo' );
			self::campaign_field( 'campaign_logo_color' );
			self::campaign_field( 'campaign_nav_type' );
			self::campaign_field( 'campaign_nav_color' );
			self::campaign_field( 'campaign_nav_border' );
			self::campaign_field( 'campaign_header_color' );
			self::campaign_field( 'campaign_primary_color' );
			self::campaign_field( 'campaign_secondary_color' );
			self::campaign_field( 'campaign_header_primary' );
			self::campaign_field( 'campaign_body_font' );
			self::campaign_field( 'campaign_footer_theme' );
			self::campaign_field( 'footer_links_color' );
		}

		/**
		 * Register Color Picker Metabox for navigation
		 */
		public function register_campaigns_metaboxes() {
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
					'id'   => 'new_sidebar_link',
					'type' => 'sidebar_link',
				]
			);

			$cmb->add_field(
				[
					'name' => __( 'Footer item 1', 'planet4-master-theme-backend' ),
					'id'   => 'campaign_footer_item1',
					'type' => 'footer_icon_link',
				]
			);

			$cmb->add_field(
				[
					'name' => __( 'Footer item 2', 'planet4-master-theme-backend' ),
					'id'   => 'campaign_footer_item2',
					'type' => 'footer_icon_link',
				]
			);

			$cmb->add_field(
				[
					'name' => __( 'Footer item 3', 'planet4-master-theme-backend' ),
					'id'   => 'campaign_footer_item3',
					'type' => 'footer_icon_link',
				]
			);

			$cmb->add_field(
				[
					'name' => __( 'Footer item 4', 'planet4-master-theme-backend' ),
					'id'   => 'campaign_footer_item4',
					'type' => 'footer_icon_link',
				]
			);

			$cmb->add_field(
				[
					'name' => __( 'Footer item 5', 'planet4-master-theme-backend' ),
					'id'   => 'campaign_footer_item5',
					'type' => 'footer_icon_link',
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

		/**
		 * CMB2 custom field(sidebar_link) callback function.
		 *
		 * @param array $field The CMB2 field array.
		 * @param array $value The CMB2 field Value.
		 * @param array $object_id The id of the object.
		 * @param array $object_type The type of object.
		 * @param array $field_type Instance of the `cmb2_Meta_Box_types` object.
		 */
		public function cmb2_render_sidebar_link_field_callback(
			$field,
			$value,
			$object_id,
			$object_type,
			$field_type
		) {
			?>
			<a
				href="#" onclick="openSidebar()"
				id="new_sidebar_link">
				<?php
					echo __( 'Design settings moved to a new sidebar.', 'planet4-master-theme-backend' )
				?>
			</a>
			<script>
				function openSidebar() {
					let sidebarButton = document.querySelector( '.edit-post-pinned-plugins button[aria-expanded=false]' );
					if ( sidebarButton ) {
						sidebarButton.click();
					}
				}
			</script>
			<?php
		}

		/**
		 * CMB2 custom field(footer_icon_link) callback function.
		 *
		 * @param array $field The CMB2 field array.
		 * @param array $value The CMB2 field Value.
		 * @param array $object_id The id of the object.
		 * @param array $object_type The type of object.
		 * @param array $field_type Instance of the `cmb2_Meta_Box_types` object.
		 */
		public function cmb2_render_footer_icon_link_field_callback( $field, $value, $object_id, $object_type, $field_type ) {
			$value = wp_parse_args(
				$value,
				[
					'url'  => '',
					'icon' => '',
				]
			);
			?>
			<div class="alignleft">
			<?php
				echo wp_kses(
					$field_type->input(
						array(
							'class'       => 'cmb-type-text-medium',
							'name'        => esc_attr( $field_type->_name( '[url]' ) ),
							'id'          => esc_attr( $field_type->_id( '_url' ) ),
							'type'        => 'text',
							'value'       => esc_url( $value['url'] ),
							'placeholder' => __( 'Footer item link', 'planet4-master-theme-backend' ),
						)
					),
					[
						'input' => [
							'class'       => [],
							'placeholder' => [],
							'name'        => [],
							'id'          => [],
							'type'        => [],
							'value'       => [],
							'data-hash'   => [],
						],
					]
				);
			?>
			</div>
			<div class="alignleft">
			<?php
				echo wp_kses(
					$field_type->input(
						array(
							'class'       => 'cmb-type-text-medium',
							'name'        => esc_attr( $field_type->_name( '[icon]' ) ),
							'id'          => esc_attr( $field_type->_id( '_icon' ) ),
							'type'        => 'text',
							'value'       => $value['icon'],
							'placeholder' => __( 'Footer icon name', 'planet4-master-theme-backend' ),
						)
					),
					[
						'input' => [
							'class'       => [],
							'placeholder' => [],
							'name'        => [],
							'id'          => [],
							'type'        => [],
							'value'       => [],
							'data-hash'   => [],
						],
					]
				);
			?>
			</div>
			<div class="alignleft"> <?php esc_html_e( 'In the “Footer icon name” field add the name of the icon you want from the', 'planet4-master-theme-backend' ); ?> <a target="_blank" href="https://github.com/greenpeace/planet4-styleguide/tree/master/src/icons"><?php esc_html_e( 'list of icons in the CSS styleguide', 'planet4-master-theme-backend' ); ?></a>. e.g. twitter-square</div>
			<?php
		}

		/**
		 * Register a key as a post_meta with the argument `show_in_rest` that is needed on all fields so they can be
		 * used through the REST api. Also set `type` and `single` as both are the same for all attributes.
		 *
		 * @param string $meta_key Identifier the post_meta field will be registered with.
		 * @param array  $args Arguments which are passed on to register_post_meta.
		 *
		 * @return void A description of the field.
		 */
		private static function campaign_field(
			string $meta_key,
			array $args = []
		): void {
			$args = array_merge(
				[
					'show_in_rest' => true,
					'type'         => 'string',
					'single'       => true,
				],
				$args
			);
			register_post_meta( self::POST_TYPE, $meta_key, $args );
		}

		/**
		 * Determine the css variables for a certain post.
		 *
		 * @param array $meta The meta containing the variable values.
		 * @return array The values that will be used for the css variables.
		 */
		public static function css_vars( array $meta ): array {
			// Set specific CSS for Montserrat.
			$special_weight_fonts = [
				'Montserrat'       => '900',
				'Montserrat_Light' => '500',
			];

			$header_primary_font = 'Montserrat_Light' === ( $meta['campaign_header_primary'] ?? null )
					? 'Montserrat'
					: $meta['campaign_header_primary'] ?? null;

			$body_font = $meta['campaign_body_font'] ?? null;

			$footer_theme = $meta['campaign_footer_theme'] ?? null;

			if ( 'white' === $footer_theme ) {
				$default_footer_links_color = $meta['campaign_nav_color'] ?: '#1A1A1A';
				$footer_links_color         = $meta['footer_links_color'] ?: $default_footer_links_color;
				$footer_color               = '#FFFFFF';
			} else {
				$footer_links_color = 'dark' === ( $meta['campaign_logo_color'] ?? null ) ? '#1A1A1A' : '#FFFFFF';
				$footer_color       = $meta['campaign_nav_color'] ?? null;
			}

			$passive_button_colors_map = [
				'#ffd204' => '#ffe467',
				'#66cc00' => '#66cc00',
				'#6ed961' => '#a7e021',
				'#21cbca' => '#77ebe0',
				'#ee562d' => '#f36d3a',
				'#7a1805' => '#a01604',
				'#2077bf' => '#2077bf',
				'#1b4a1b' => '#1b4a1b',
			];

			if ( 'minimal' === $meta['campaign_nav_type'] && isset( $meta['campaign_nav_color'] ) ) {
				$nav_color = $meta['campaign_nav_color'];
			} else {
				$nav_color = null;
			}

			return [
				'nav-color'            => $nav_color,
				'footer-color'         => $footer_color,
				'footer-links-color'   => $footer_links_color,
				'header-color'         => $meta['campaign_header_color'] ?? null,
				'header-primary-font'  => $header_primary_font,
				'header-font-weight'   => $special_weight_fonts[ $meta['campaign_header_primary'] ?? null ] ?? null,
				'body-font'            => $body_font,
				'passive-button-color' => isset( $meta['campaign_primary_color'] ) && $meta['campaign_primary_color']
					? $passive_button_colors_map[ strtolower( $meta['campaign_primary_color'] ) ]
					: null,
				'primary-color'        => $meta['campaign_primary_color'] ?? null,
				'secondary-color'      => $meta['campaign_secondary_color'] ?? null,
			];
		}

		/**
		 * Get the logo based on the meta settings. Ensures that no other campaign logo will be used even if that's the value stored.
		 *
		 * @param array $meta The meta containing the style settings.
		 * @return string The identifier of the logo.
		 */
		public static function get_logo( array $meta ): string {
			$logo = $meta['campaign_logo'] ?? null;
			if ( ! $logo ) {
				return 'greenpeace';
			}
			$theme = $meta['theme'] ?: $meta['_campaign_page_template'] ?: 'default';
			if ( $theme !== 'default' ) {
				return $logo === 'greenpeace' ? 'greenpeace' : $theme;
			}

			return $logo ?: 'greenpeace';
		}
	}
}
