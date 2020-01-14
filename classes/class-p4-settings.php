<?php
/**
 * Settings Class
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_Settings' ) ) {
	/**
	 * Class P4_Settings
	 */
	class P4_Settings {

		/**
		 * Option key, and option page slug
		 *
		 * @var string
		 */
		private $key = 'planet4_options';

		/**
		 * Array of metaboxes/fields
		 *
		 * @var array
		 */
		protected $option_metabox = [];

		/**
		 * Options Page title
		 *
		 * @var string
		 */
		protected $title = '';

		/**
		 * Options Page hook
		 *
		 * @var string
		 */
		protected $options_page = '';

		/**
		 * Constructor
		 */
		public function __construct() {

			// Set our title.
			$this->title = __( 'Planet4', 'planet4-master-theme-backend' );

			$this->fields = [
				[
					'name' => __( 'Website Navigation Title', 'planet4-master-theme-backend' ),
					'id'   => 'website_navigation_title',
					'type' => 'text',
				],
				[
					'name' => __( 'Select Act Page', 'planet4-master-theme-backend' ),
					'id'   => 'act_page',
					'type' => 'act_page_dropdown',
				],

				[
					'name' => __( 'Select Explore Page', 'planet4-master-theme-backend' ),
					'id'   => 'explore_page',
					'type' => 'explore_page_dropdown',
				],

				[
					'name' => __( 'Select Issues Parent Category', 'planet4-master-theme-backend' ),
					'id'   => 'issues_parent_category',
					'type' => 'category_select_taxonomy',
				],

				[
					'name'    => __( 'Copyright Text Line 1', 'planet4-master-theme-backend' ),
					'id'      => 'copyright_line1',
					'type'    => 'wysiwyg',
					'options' => [
						'textarea_rows' => 3,
						'media_buttons' => false,
					],
				],

				[
					'name'    => __( 'Copyright Text Line 2', 'planet4-master-theme-backend' ),
					'id'      => 'copyright_line2',
					'type'    => 'wysiwyg',
					'options' => [
						'textarea_rows' => 2,
						'media_buttons' => false,
					],
				],

				[
					'name' => __( 'Google Tag Manager Container', 'planet4-master-theme-backend' ),
					'id'   => 'google_tag_manager_identifier',
					'type' => 'text',
				],

				[
					'name' => __( 'Google Optimize anti-flicker snippet', 'planet4-master-theme-backend' ),
					'desc' => __( 'It will include the relevant snippet for A/B testing.' ),
					'id'   => 'google_optimizer',
					'type' => 'checkbox',
				],

				[
					'name' => __( 'Engaging Networks Subscribe Form URL', 'planet4-master-theme-backend' ),
					'id'   => 'engaging_network_form_id',
					'type' => 'text',
				],

				[
					'name' => __( 'Facebook Page ID', 'planet4-master-theme-backend' ),
					'id'   => 'facebook_page_id',
					'type' => 'text',
				],

				[
					'name'    => __( 'Cookies Text', 'planet4-master-theme-backend' ),
					'id'      => 'cookies_field',
					'type'    => 'wysiwyg',
					'options' => [
						'textarea_rows' => 5,
						'media_buttons' => false,
					],
				],

				[
					'name' => __( 'Default title for related articles block', 'planet4-master-theme-backend' ),
					'id'   => 'articles_block_title',
					'type' => 'text',
					'desc' => __( 'If no title set for <b>Article Block</b>, the default title will appear.', 'planet4-master-theme-backend' ),
				],

				[
					'name' => __( 'Default button title for related articles block', 'planet4-master-theme-backend' ),
					'id'   => 'articles_block_button_title',
					'type' => 'text',
					'desc' => __( 'If no button title set for <b>Article Block</b>, the default button title will appear.', 'planet4-master-theme-backend' ),
				],

				[
					'name'       => __( 'Default Number Of Related Articles', 'planet4-master-theme-backend' ),
					'id'         => 'articles_count',
					'type'       => 'text',
					'attributes' => [
						'type' => 'number',
					],
					'desc'       => __( 'If no number of Related Articles set for <b>Article Block</b>, the default number of Related Articles will appear.', 'planet4-master-theme-backend' ),
				],

				[
					'name'       => __( 'Take Action Covers default button text', 'planet4-master-theme-backend' ),
					'id'         => 'take_action_covers_button_text',
					'type'       => 'text',
					'attributes' => [
						'type' => 'text',
					],
					'desc'       => __(
						'Add default button text which appears on <b>Take Action</b> card of <b>Take Action Covers</b> block. <br>
					                     Also it would be used for Take Action Cards inside Posts and Take Action Cards in search results',
						'planet4-master-theme-backend'
					),
				],

				[
					'name'       => __( 'Donate button link', 'planet4-master-theme-backend' ),
					'id'         => 'donate_button',
					'type'       => 'text',
					'attributes' => [
						'type' => 'text',
					],
				],

				[
					'name'       => __( 'Donate button text', 'planet4-master-theme-backend' ),
					'id'         => 'donate_text',
					'type'       => 'text',
					'attributes' => [
						'type' => 'text',
					],
				],

				[
					'name'       => __( '404 Background Image', 'planet4-master-theme-backend' ),
					'id'         => '404_page_bg_image',
					'type'       => 'file',
					'options'    => [
						'url' => false,
					],
					'text'       => [
						'add_upload_file_text' => __( 'Add 404 Page Background Image', 'planet4-master-theme-backend' ),
					],
					'query_args' => [
						'type' => 'image',
					],
					'desc'       => __( 'Minimum image width should be 1920px', 'planet4-master-theme-backend' ),
				],

				[
					'name'    => __( '404 Page text', 'planet4-master-theme-backend' ),
					'id'      => '404_page_text',
					'type'    => 'wysiwyg',
					'options' => [
						'textarea_rows' => 3,
						'media_buttons' => false,
					],
					'desc'    => __( 'Add 404 page text', 'planet4-master-theme-backend' ),
				],

				[
					'name' => __( 'Default P4 Post Type', 'planet4-master-theme-backend' ),
					'id'   => 'default_p4_pagetype',
					'type' => 'pagetype_select_taxonomy',
				],

				[
					'name'       => __( 'Default Happy Point Background Image', 'planet4-master-theme-backend' ),
					'id'         => 'happy_point_bg_image',
					'type'       => 'file',
					'options'    => [
						'url' => false,
					],
					'text'       => [
						'add_upload_file_text' => __( 'Add Default Happy Point Background Image', 'planet4-master-theme-backend' ),
					],
					'query_args' => [
						'type' => 'image',
					],
					'desc'       => __( 'Minimum image width should be 1920px', 'planet4-master-theme-backend' ),
				],
				[
					'name' => __( 'Enforce Cookies Policy', 'planet4-master-theme-backend' ),
					'desc' => __( 'GDPR related setting. By enabling this option specific content will be blocked and will require user consent to be shown.', 'planet4-master-theme-backend' ),
					'id'   => 'enforce_cookies_policy',
					'type' => 'checkbox',
				],
				[
					'name' => __( 'Include archived content in search results', 'planet4-master-theme-backend' ),
					'desc' => __( 'By enabling this option content from the archived P3 site will be included in P4 search results if users selects to include them.', 'planet4-master-theme-backend' ),
					'id'   => 'include_archive_content',
					'type' => 'checkbox',
				],
				[
					'name'       => __( 'Include Archive content command text', 'planet4-master-theme-backend' ),
					'id'         => 'include_archive_content_text',
					'type'       => 'text',
					'attributes' => [
						'type' => 'text',
					],
					'desc'       => __( 'Defualt text of the command is "INCLUDE ARCHIVE CONTENT"', 'planet4-master-theme-backend' ),
				],
				[
					'name'       => __( 'Exclude Archive content command text', 'planet4-master-theme-backend' ),
					'id'         => 'exclude_archive_content_text',
					'type'       => 'text',
					'attributes' => [
						'type' => 'text',
					],
					'desc'       => __( 'Defualt text of the command is "EXCLUDE ARCHIVE CONTENT"', 'planet4-master-theme-backend' ),
				],
			];
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
			add_filter( 'cmb2_render_pagetype_select_taxonomy', [ $this, 'p4_render_pagetype_dropdown' ], 10, 2 );

			// Make settings multilingual if wpml plugin is installed and activated.
			if ( function_exists( 'icl_object_id' ) ) {
				add_action( 'init', [ $this, 'make_settings_multilingual' ] );
			}
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
		 *
		 * @param array  $field_args Field arguments.
		 * @param string $value Value.
		 */
		public function p4_render_act_page_dropdown( $field_args, $value ) {
			wp_dropdown_pages(
				[
					'show_option_none' => __( 'Select Page', 'planet4-master-theme-backend' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'selected'         => $value,
					'name'             => 'act_page',
				]
			);
		}

		/**
		 * Render explore page dropdown.
		 *
		 * @param array  $field_args Field arguments.
		 * @param string $value Value.
		 */
		public function p4_render_explore_page_dropdown( $field_args, $value ) {
			wp_dropdown_pages(
				[
					'show_option_none' => __( 'Select Page', 'planet4-master-theme-backend' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'selected'         => $value,
					'name'             => 'explore_page',
				]
			);
		}

		/**
		 * Render category dropdown.
		 *
		 * @param array  $field_args Field arguments.
		 * @param string $value Value.
		 */
		public function p4_render_category_dropdown( $field_args, $value ) {

			wp_dropdown_categories(
				[
					'show_option_none' => __( 'Select Category', 'planet4-master-theme-backend' ),
					'hide_empty'       => 0,
					'hierarchical'     => true,
					'orderby'          => 'name',
					'selected'         => $value,
					'name'             => 'issues_parent_category',
				]
			);
		}

		/**
		 * Render p4-pagetype dropdown.
		 *
		 * @param CMB2_Field $field_args CMB2 field Object.
		 * @param int        $value Pagetype taxonomy ID.
		 */
		public function p4_render_pagetype_dropdown( $field_args, $value ) {

			wp_dropdown_categories(
				[
					'show_option_none' => __( 'Select Posttype', 'planet4-master-theme-backend' ),
					'hide_empty'       => 0,
					'orderby'          => 'name',
					'selected'         => $value,
					'name'             => 'default_p4_pagetype',
					'taxonomy'         => 'p4-page-type',
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
		 *
		 * @return array
		 */
		public function option_metabox() {
			return [
				'id'         => 'option_metabox',
				'show_on'    => [
					'key'   => 'options-page',
					'value' => [
						$this->key,
					],
				],
				'show_names' => true,
				'fields'     => $this->fields,
			];
		}

		/**
		 * Hook for wpml plugin.
		 * Enables the possibility to save a different value per language for the theme options using WPML language switcher.
		 */
		public function make_settings_multilingual() {
			do_action( 'wpml_multilingual_options', 'planet4_options' );
		}
	}
}

/**
 * Wrapper function around cmb2_get_option.
 *
 * @param  string $key Options array key.
 * @return mixed Option value.
 */
function planet4_get_option( $key = '' ) {
	if ( function_exists( 'cmb2_get_option' ) ) {
		return cmb2_get_option( 'planet4_options', $key );
	} else {
		$options = get_option( 'planet4_options' );
		return isset( $options[ $key ] ) ? $options[ $key ] : false;
	}
}
