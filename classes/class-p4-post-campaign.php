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

		public const META_FIELDS = [
			'theme',
			'campaign_logo',
			'campaign_logo_color',
			'campaign_nav_type',
			'campaign_nav_color',
			'campaign_nav_border',
			'campaign_header_color',
			'campaign_primary_color',
			'campaign_secondary_color',
			'campaign_header_primary',
			'campaign_body_font',
			'campaign_footer_theme',
			'footer_links_color',
		];

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

			add_filter( 'get_user_option_edit_campaign_per_page', [ $this, 'set_default_items_per_page' ], 10, 3 );

		}

		/**
		 * Increase the maximum number of items displayed so that there are enough items to collapse any child pages.
		 *
		 * @param int|null $result Possibly value chosen by the current user.
		 * @param string   $option The name of the option.
		 * @param object   $user The current user.
		 * @return int The amount of pages that will be used.
		 */
		public function set_default_items_per_page( $result, $option, $user ) {
			if ( (int) $result < 1 ) {
				return 200;
			}
			return $result;
		}

		/**
		 * Register campaigns cpt
		 */
		public function register_campaigns_cpt() {

			$labels = [
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
				'parent_item_colon'  => __( 'Parent Campaign:', 'planet4-master-theme-backend' ),
				'not_found'          => __( 'No campaigns found.', 'planet4-master-theme-backend' ),
				'not_found_in_trash' => __( 'No campaigns found in Trash.', 'planet4-master-theme-backend' ),
			];

			$args = [
				'labels'             => $labels,
				'description'        => __( 'Campaigns', 'planet4-master-theme-backend' ),
				'public'             => true,
				'publicly_queryable' => true,
				'show_ui'            => true,
				'show_in_menu'       => true,
				'query_var'          => true,
				'rewrite'            => [
					'slug'       => 'campaign',
					'with_front' => false,
				],
				'capability_type'    => [ 'campaign', 'campaigns' ],
				'map_meta_cap'       => true,
				'has_archive'        => true,
				'hierarchical'       => true,
				'show_in_nav_menus'  => true,
				'menu_position'      => null,
				'menu_icon'          => 'dashicons-megaphone',
				'show_in_rest'       => true,
				'supports'           => [
					'page-attributes',
					'title',
					'editor',
					'author',
					'thumbnail',
					'excerpt',
					'revisions',
					// Required to expose meta fields in the REST API.
					'custom-fields',
				],
			];

			register_post_type( self::POST_TYPE, $args );

			foreach ( self::META_FIELDS as $field ) {
				self::campaign_field( $field );
			}
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
			wp_register_style( 'cmb-style', get_template_directory_uri() . '/admin/css/campaign.css', [], '0.1' );
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
					esc_html_e( 'Design settings moved to a new sidebar.', 'planet4-master-theme-backend' )
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
						[
							'class'       => 'cmb-type-text-medium',
							'name'        => esc_attr( $field_type->_name( '[url]' ) ),
							'id'          => esc_attr( $field_type->_id( '_url' ) ),
							'type'        => 'text',
							'value'       => esc_url( $value['url'] ),
							'placeholder' => __( 'Footer item link', 'planet4-master-theme-backend' ),
						]
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
						[
							'class'       => 'cmb-type-text-medium',
							'name'        => esc_attr( $field_type->_name( '[icon]' ) ),
							'id'          => esc_attr( $field_type->_id( '_icon' ) ),
							'type'        => 'text',
							'value'       => $value['icon'],
							'placeholder' => __( 'Footer icon name', 'planet4-master-theme-backend' ),
						]
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
		 * Gets the default for a field.
		 *
		 * @param array $field A field from the JSON theme file.
		 * @return string Default value
		 */
		private static function get_field_default( $field ) {
			$default = null;
			if ( isset( $field['configurations'] ) && isset( $field['configurations']['default'] ) ) {
				$default_configuration = $field['configurations']['default'];
				if ( isset( $field['configurations'][ $default_configuration ]['default'] ) ) {
					$default = $field['configurations'][ $default_configuration ]['default'];
				}
			} elseif ( isset( $field['default'] ) ) {
				$default = $field['default'];
			}

			return $default;
		}

		/**
		 * Get the theme defaults
		 *
		 * @param mixed $theme_json The JSON theme file.
		 */
		private static function get_theme_defaults( $theme_json ) {
			$defaults = [];
			foreach ( $theme_json['fields'] as $field ) {
				$defaults[ $field['id'] ] = self::get_field_default( $field );
			}

			return $defaults;
		}

		/**
		 * Determine the css variables for a certain post.
		 *
		 * @param array $meta The meta containing the variable values.
		 * @return array The values that will be used for the css variables.
		 */
		public static function css_vars( array $meta ): array {
			$theme = self::get_theme( $meta );

			// TODO: Use wp_safe_remote_get?
			// TODO: Handle errors.
			$theme_json = json_decode(
				// Ignoring the PHPCS error in the next line because it's a local file, not a remote request.
				wp_safe_remote_get( __DIR__ . '/../campaign_themes/' . $theme . '.json' ), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
				true
			);

			$defaults = self::get_theme_defaults( $theme_json );

			// Replace the defaults with the campaign options where applicable.
			$intersect = array_intersect_key( $defaults, $meta );
			$css_vars  = array_merge( $defaults, $intersect );

			$css_vars = array_merge( $css_vars, self::get_footer_theme( $meta ) );
			$css_vars = array_merge( $css_vars, self::get_passive_button_color( $meta, $defaults ) );
			$css_vars = array_merge( $css_vars, self::get_body_font( $meta, $defaults ) );
			$css_vars = self::replace_font_aliases( $css_vars );

			$css_vars = array_filter( $css_vars );

			return $css_vars;
		}

		/**
		 * @deprecated This function replaces some arbitrary font names in the CSS variables.
		 *
		 * @param array $css_vars The array containing the CSS variables.
		 * @return array The variables for the footer theme.
		 */
		public static function replace_font_aliases( array $css_vars ): array {
			// TODO: Remove these special cases.
			if ( isset( $css_vars['campaign_header_primary'] ) ) {
				$css_vars['campaign_header_primary'] = str_replace( 'Montserrat_Light', 'Montserrat', $css_vars['campaign_header_primary'] );
			}

			if ( isset( $css_vars['campaign_body_font'] ) ) {
				$css_vars['campaign_body_font'] = str_replace( 'Montserrat_Light', 'Montserrat', $css_vars['campaign_body_font'] );
			}

			return $css_vars;
		}

		/**
		 * DEPRECATE: Get the passive button color based on the meta settings.
		 *
		 * @param array $meta The meta containing the style settings.
		 * @return array The variables for the passive button.
		 */
		public static function get_passive_button_color( array $meta ): array {
			// TODO: Remove this "Passive" color map based on hovers.
			$passive_button_colors_map = [
				'#ffd204' => '#f36d3a',
				'#ffd204' => '#ffe467',
				'#66cc00' => '#66cc00',
				'#6ed961' => '#a7e021',
				'#21cbca' => '#77ebe0',
				'#7a1805' => '#a01604',
				'#2077bf' => '#2077bf',
				'#1b4a1b' => '#1b4a1b',
			];

			$css_vars = [];

			$css_vars['passive_button_color'] = isset( $meta['campaign_primary_color'] ) && $meta['campaign_primary_color']
			? $passive_button_colors_map[ strtolower( $meta['campaign_primary_color'] ) ]
			: '#f36d3a';

			return $css_vars;
		}

		/**
		 * DEPRECATE: Replaces the legacy mapping to add missing or composed variables,
		 * returns an iterable flat set of variables.
		 *
		 * @param array $css_vars The array containing the CSS variables.
		 * @return array The variables for the footer theme.
		 */
		public static function transform_legacy_mapping( array $css_vars ): array {
			$css_vars['campaigns-header-primary-font'] = $css_vars['campaign_header_primary'];
			$css_vars['campaigns-header-font-weight']  = $css_vars['campaign_header_weight'];

			$css_vars['campaigns-primary-button-color-idle']    = $css_vars['passive_button_color'];
			$css_vars['campaigns-primary-button-color-hover']   = $css_vars['campaign_primary_color'];
			$css_vars['campaigns-secondary-button-color-idle']  = $css_vars['campaign_secondary_color'];
			$css_vars['campaigns-secondary-button-color-hover'] = $css_vars['campaign_secondary_color'];

			return $css_vars;
		}

		/**
		 * Get the footer variables based on the meta settings.
		 *
		 * @param array $meta The meta containing the style settings.
		 * @return array The variables for the footer theme.
		 */
		public static function get_footer_theme( array $meta ): array {
			$footer_theme = ! empty( $meta['footer_theme'] )
												? $meta['footer_theme']
												: null;

			$css_vars = [];

			if ( 'white' === $footer_theme ) {
				$default_footer_links_color     = $meta['campaign_nav_color'] ? $meta['campaign_nav_color'] : '#1A1A1A';
				$css_vars['footer_links_color'] = $meta['footer_links_color'] ? $meta['footer_links_color'] : $default_footer_links_color;
				$css_vars['footer_color']       = '#FFFFFF';
			} else {
				switch ( ( $meta['campaign_logo_color'] ?? null ) ) {
					case 'dark':
						$css_vars['footer_links_color'] = '#1A1A1A';
						break;
					case 'green':
						$css_vars['footer_links_color'] = '#2caf4e';
						break;
					default:
						$css_vars['footer_links_color'] = '#FFFFFF';
				}
			}

			return $css_vars;
		}

		/**
		 * Get the theme based on the meta settings.
		 *
		 * @param array $meta The meta containing the style settings.
		 * @return string The identifier of the theme.
		 */
		public static function get_theme( array $meta ): string {
			$theme = $meta['theme'] ?? $meta['_campaign_page_template'] ?? null;
			$theme = $theme ? $theme : 'default';

			return $theme;
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

			$theme = self::get_theme( $meta );

			if ( 'default' !== $theme ) {
				return 'greenpeace' === $logo ? 'greenpeace' : $theme;
			}

			return $logo ? $logo : 'greenpeace';
		}
	}
}
