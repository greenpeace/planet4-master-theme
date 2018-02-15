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

			$this->fields = [
				[
					'name'    => __( 'Select Act Page', 'planet4-master-theme' ),
					'id'      => 'act_page',
					'type'    => 'act_page_dropdown',
				],

				[
					'name'    => __( 'Select Explore Page', 'planet4-master-theme' ),
					'id'      => 'explore_page',
					'type'    => 'explore_page_dropdown',
				],

				[
					'name'    => __( 'Select Issues Parent Category', 'planet4-master-theme' ),
					'id'      => 'issues_parent_category',
					'type'    => 'category_select_taxonomy',
				],

				[
					'name'    => __( 'Copyright Text', 'planet4-master-theme' ),
					'id'      => 'copyright',
					'type'    => 'wysiwyg',
					'options' => [
						'textarea_rows' => 3,
						'media_buttons' => false,
					],
				],

				[
					'name'    => __( 'Google Tag Manager Identifier', 'planet4-master-theme' ),
					'id'      => 'google_tag_manager_identifier',
					'type'    => 'text',
				],

				[
					'name'    => __( 'Engaging Network ID', 'planet4-master-theme' ),
					'id'      => 'engaging_network_form_id',
					'type'    => 'text',
				],

				[
					'name'    => __( 'Cookies Text', 'planet4-master-theme' ),
					'id'      => 'cookies_field',
					'type'    => 'wysiwyg',
					'options' => [
						'textarea_rows' => 5,
						'media_buttons' => false
					 ],
				],

				[
					'name'    => __( 'Default title for related articles block in post', 'planet4-master-theme' ),
					'id'      => 'articles_block_title',
					'type'    => 'text',
				],

				[
					'name'       => __( 'Default Number Of Related Articles In Post', 'planet4-master-theme' ),
					'id'         => 'articles_count',
					'type'       => 'text',
					'attributes' => array(
						'type' => 'number',
					),
				],

				[
					'name'       => __( 'Donate button link', 'planet4-master-theme' ),
					'id'         => 'donate_button',
					'type'       => 'text',
					'attributes' => [
						'type' => 'text',
					],
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
			add_action( 'registered_taxonomy', [ $this,'add_p4_page_types_categories_fields'] );
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

		/**
		 * Register fields for mapping between planet4 page types and categories.
		 * Hook for p4-page-type taxonomy register.
		 */
		public function add_p4_page_types_categories_fields( $taxonomy ) {
			if ( 'p4-page-type' !== $taxonomy ) {
				return;
			}

			$p4        = [];
			$i         = 1;
			$all_types = get_terms( [ 'taxonomy' => 'p4-page-type', 'hide_empty' => false ] );
			foreach ( $all_types as $term ) {
				$temp_attributes = [
					'name'           => $term->name,
					// translators: placeholder is a term which does not need translation in context.
					'desc'           => sprintf( __( 'Map %s planet4 page type to a category' ), $term->name ),
					'id'             => 'p4_page_type_' . $term->slug . '_category',
					'taxonomy'       => 'category',
					'type'           => 'taxonomy_select',
					'remove_default' => 'true',
				];
				if ( $i === 1 ) {
					$temp_attributes['before_row'] = '<hr><p>' .
													 __( 'Planet4 page types - Categories mapping' ) .
													 '</p><p>' .
													 __( 'When a post is assigned to one of the selected categories, 
													      the post will be assigned the mapped planet4 page type.' ) .
													 '</p>';
				}
				if ( $i === count( $all_types ) ) {
					$temp_attributes['after_row'] = '<hr>';
				}
				$p4[] = $temp_attributes;
				$i++;
			}
			$p4[]         = [
				'id'   => 'p4-page-types-mapping',
				'type' => 'hidden',
			];
			$this->fields = array_merge( $this->fields, $p4 );
			$this->fields = apply_filters( 'planet4_options', $this->fields );
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
