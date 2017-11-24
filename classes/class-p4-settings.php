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
		protected $option_metabox = array();

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
		 * @since 0.1.0
		 */
		public function __construct() {

			// Set our title
			$this->title   = __( 'Planet4', 'planet4-master-theme' );
			$pages         = get_pages();

			$pages_array[] = __( 'Select Page' ,'planet4-master-theme' ) ;
			foreach ( $pages as $all_pages ) {
				$pages_array[$all_pages->ID] = __( $all_pages->post_title ,'planet4-master-theme' ) ;
			}

			$categories         = get_categories();

			$categories_array[] = __( 'Select Category' ,'planet4-master-theme' ) ;
			foreach ( $categories as $category ) {
				$categories_array[$category->term_id] = __( $category->cat_name ,'planet4-master-theme' );
			}

			// Set our CMB2 fields, wrap them in a filter so others can easily tap in and add their own as well.
			$this->fields = apply_filters( 'planet4_options', array(
					array(
						'name'    => __( 'Select act page', 'planet4-master-theme' ),
						'id'      => 'select_act_page',
						'type'    => 'select',
						'options' => $pages_array
					),

					array(
						'name'    => __( 'Select explore page', 'planet4-master-theme' ),
						'id'      => 'select_explore_page',
						'type'    => 'select',
						'options' => $pages_array
					),

					array(
						'name'    => __( 'Select category', 'planet4-master-theme' ),
						'id'      => 'select_category',
						'type'    => 'select',
						'options' => $categories_array
					)

				) );
			$this->hooks();
		}

		/**
		 * Initiate our hooks
		 * @since 0.1.0
		 */
		public function hooks() {
			add_action( 'admin_init', array( $this, 'init' ) );
			add_action( 'admin_menu', array( $this, 'add_options_page' ) );
		}

		/**
		 * Register our setting to WP
		 * @since  1.0
		 */
		public function init() {
			register_setting( $this->key, $this->key );
		}

		/**
		 * Add menu options page
		 * @since 0.1.0
		 */
		public function add_options_page() {
			$this->options_page = add_options_page( $this->title, $this->title, 'manage_options', $this->key, array( $this, 'admin_page_display' ) );
		}

		/**
		 * Admin page markup. Mostly handled by CMB2
		 * @since  1.0
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
		 * Defines the theme option metabox and field configuration
		 * @since  1.0
		 * @return array
		 */
		public function option_metabox() {
			return array(
				'id'         => 'option_metabox',
				'show_on'    => array( 'key' => 'options-page', 'value' => array( $this->key, ), ),
				'show_names' => true,
				'fields'     => $this->fields,
			);
		}

		/**
		 * Public getter method for retrieving protected/private variables
		 * @since  1.0
		 * @param  string  $field Field to retrieve
		 * @return mixed          Field value or exception is thrown
		 */
		public function __get( $field ) {
			// Allowed fields to retrieve
			if ( in_array( $field, array( 'key', 'fields', 'title', 'options_page' ), true ) ) {
				return $this->{$field};
			}

			if ( 'option_metabox' === $field ) {
				return $this->option_metabox();
			}

			throw new Exception( 'Invalid property: ' . $field );
		}
	}
}

/**
 * Wrapper function around cmb2_get_option
 * @since  1.0
 * @param  string  $key Options array key
 * @return mixed        Option value
 */
function planet4_get_option( $key = '' ) {
	global $planet4_options;

	if( function_exists( 'cmb2_get_option' ) ) {
		return cmb2_get_option( 'planet4_options', $key );
	} else {
		$options = get_option( 'planet4_options' );
		return isset( $options[ $key ] ) ? $options[ $key ] : false;
	}
}
