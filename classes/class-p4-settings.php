<?php

if ( ! class_exists( 'P4_Settings' ) ) {
	/**
	 * Class P4_Settings
	 */
	class P4_Settings {

		/**
		 * Option key, and option page slug
		 * @var string
		 */
		private $key = 'planet4_options';

		/**
		 * Array of metaboxes/fields
		 * @var array
		 */
		protected $option_metabox = [];

		/**
		 * Options Page title
		 * @var string
		 */
		protected $title = '';

		/**
		 * Options Page hook
		 * @var string
		 */
		protected $options_page = '';

		/**
		 * Constructor
		 */
		public function __construct() {

			// Set our title
			$this->title   = __( 'Planet4', 'planet4-master-theme' );

			$this->fields = apply_filters( 'planet4_options', [
				[
					'name'    => __( 'Select act page', 'planet4-master-theme' ),
					'id'      => 'act_page',
					'type'    => 'act_page_dropdown',
				],

				[
					'name'    => __( 'Select explore page', 'planet4-master-theme' ),
					'id'      => 'explore_page',
					'type'    => 'explore_page_dropdown',
				],

				[
					'name'    => __( 'Select issues parent category', 'planet4-master-theme' ),
					'id'      => 'issues_parent_category',
					'type'    => 'category_select_taxonomy',
				],
			] );
			$this->hooks();
		}

		/**
		 * Initiate our hooks
		 */
		public function hooks() {
			add_action( 'admin_init', [ $this, 'init' ] );
			add_action( 'admin_menu', [ $this, 'add_options_page' ] );
			add_filter( 'cmb2_render_act_page_dropdown', [ $this, 'p4_render_act_page_dropdown' ], 10, 2 );
			add_filter( 'cmb2_render_explore_page_dropdown', [ $this, 'p4_render_explore_page_dropdown' ], 10, 2 );
			add_filter( 'cmb2_render_category_select_taxonomy', [ $this, 'p4_render_category_dropdown' ], 10, 2 );
		}

		/**
		 * Register our setting to WP.
		 */
		public function init() {
			register_setting( $this->key, $this->key );
		}

		/**
		 * Add menu options page.
		 */
		public function add_options_page() {
			$this->options_page = add_options_page( $this->title, $this->title, 'manage_options', $this->key, [ $this, 'admin_page_display' ] );
		}

		/**
		 * Render act page dropdown.
		 */
		public function p4_render_act_page_dropdown( $field_args, $value ) {
			wp_dropdown_pages( [
					'show_option_none' => __( 'Select Page', 'planet4-master-theme' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'selected'         => $value ,
					'name'             => 'act_page',
				]
			);
		}

		/**
		 * Render explore page dropdown.
		 */
		public function p4_render_explore_page_dropdown( $field_args, $value ) {
			wp_dropdown_pages( [
					'show_option_none' => __( 'Select Page', 'planet4-master-theme' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'selected'         => $value ,
					'name'             => 'explore_page',
				]
			);
		}

		/**
		 * Render category dropdown.
		 */
		public function p4_render_category_dropdown( $field_args, $value ) {
			wp_dropdown_categories( [
					'show_option_none' => __( 'Select Category', 'planet4-master-theme' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'orderby'          => 'name',
					'selected'         => $value ,
					'name'             => 'issues_parent_category',
				]
			);
		}

		/**
		 * Admin page markup. Mostly handled by CMB2.
		 */
		public function admin_page_display() {
			?>
			<div class="wrap <?php echo $this->key; ?>">
				<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
				<?php cmb2_metabox_form( $this->option_metabox(), $this->key ); ?>
			</div>
			<?php
		}

		/**
		 * Defines the theme option metabox and field configuration.
		 * @return array
		 */
		public function option_metabox() {
			return [
				'id'         => 'option_metabox',
				'show_on'    => [
					'key'      => 'options-page',
					'value'    => [
						$this->key,
					],
				],
				'show_names' => true,
				'fields'     => $this->fields,
			];
		}
	}
}

/**
 * Wrapper function around cmb2_get_option.
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
function planet4_get_option( $key = '' ) {
	if( function_exists( 'cmb2_get_option' ) ) {
		return cmb2_get_option( 'planet4_options', $key );
	} else {
		$options = get_option( 'planet4_options' );
		return isset( $options[ $key ] ) ? $options[ $key ] : false;
	}
}
