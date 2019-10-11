<?php
/**
 * Enform Post Controller class
 *
 * @package P4GEN\Controllers
 */

namespace P4GEN\Controllers\Menu;

use P4GEN\Controllers\Enform_Fields_List_Table;
use P4GEN\Controllers\Enform_Questions_List_Table;
use P4GEN\Controllers\Ensapi_Controller as Ensapi;

if ( ! class_exists( 'Enform_Post_Controller' ) ) {

	/**
	 * Class Enform_Post_Controller
	 *
	 * Creates and registers p4_en custom post type.
	 * Also add filters for p4_en list page.
	 */
	class Enform_Post_Controller extends Controller {

		/**
		 * Post type name.
		 */
		const POST_TYPE = 'p4en_form';

		/**
		 * Custom meta field where fields configuration is saved to.
		 */
		const FIELDS_META = 'p4enform_fields';

		/**
		 * Hooks all the needed functions to load the class.
		 */
		public function load() {
			parent::load();
			// Register the hooks.
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'init', [ $this, 'register_post_type' ] );
			add_shortcode( self::POST_TYPE, [ $this, 'handle_form_shortcode' ] );
			add_filter( 'post_row_actions', [ $this, 'modify_post_row_actions' ], 10, 2 );

			add_action( 'add_meta_boxes', [ $this, 'add_form_meta_box' ], 10 );
			add_action( 'add_meta_boxes', [ $this, 'add_selected_meta_box' ], 11 );
			add_action( 'add_meta_boxes', [ $this, 'add_fields_meta_box' ], 12 );
			add_action( 'add_meta_boxes', [ $this, 'add_questions_custom_box' ] );
			add_action( 'add_meta_boxes', [ $this, 'add_optins_custom_box' ] );
			add_action( 'save_post_' . self::POST_TYPE, [ $this, 'save_fields_meta_box' ], 10, 2 );

			add_action( 'wp_ajax_get_supporter_question_by_id', [ $this, 'get_supporter_question_by_id' ] );
			add_action( 'wp_ajax_nopriv_get_supporter_question_by_id', [ $this, 'get_supporter_question_by_id' ] );
		}

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) {

				add_submenu_page(
					P4GEN_PLUGIN_SLUG_NAME,
					__( 'All EN Forms', 'planet4-gutenberg-engagingnetworks' ),
					__( 'All EN Forms', 'planet4-gutenberg-engagingnetworks' ),
					'edit_posts',
					'edit.php?post_type=' . self::POST_TYPE
				);

				add_submenu_page(
					P4GEN_PLUGIN_SLUG_NAME,
					__( 'Add New', 'planet4-gutenberg-engagingnetworks' ),
					__( 'Add New', 'planet4-gutenberg-engagingnetworks' ),
					'edit_posts',
					'post-new.php?post_type=' . self::POST_TYPE
				);

				// Set hook after screen is determined to load assets for add/edit page.
				add_action( 'current_screen', [ $this, 'load_assets' ] );
			}
		}

		/**
		 * Register en forms custom post type.
		 */
		public function register_post_type() {

			$labels = array(
				'name'               => _x( 'Engaging Network Forms', 'en forms', 'planet4-gutenberg-engagingnetworks' ),
				'singular_name'      => _x( 'Engaging Network Form', 'en form', 'planet4-gutenberg-engagingnetworks' ),
				'menu_name'          => _x( 'En Forms Menu', 'admin menu', 'planet4-gutenberg-engagingnetworks' ),
				'name_admin_bar'     => _x( 'En Form', 'add new on admin bar', 'planet4-gutenberg-engagingnetworks' ),
				'add_new'            => _x( 'Add New', 'en form', 'planet4-gutenberg-engagingnetworks' ),
				'add_new_item'       => __( 'Add New EN Form', 'planet4-gutenberg-engagingnetworks' ),
				'new_item'           => __( 'New EN Form', 'planet4-gutenberg-engagingnetworks' ),
				'edit_item'          => __( 'Edit EN Form', 'planet4-gutenberg-engagingnetworks' ),
				'view_item'          => __( 'View EN Form', 'planet4-gutenberg-engagingnetworks' ),
				'all_items'          => __( 'All EN Forms', 'planet4-gutenberg-engagingnetworks' ),
				'search_items'       => __( 'Search EN Forms', 'planet4-gutenberg-engagingnetworks' ),
				'parent_item_colon'  => __( 'Parent EN Forms:', 'planet4-gutenberg-engagingnetworks' ),
				'not_found'          => __( 'No en forms found.', 'planet4-gutenberg-engagingnetworks' ),
				'not_found_in_trash' => __( 'No en forms found in Trash.', 'planet4-gutenberg-engagingnetworks' ),
			);

			register_post_type(
				self::POST_TYPE,
				[
					'labels'              => $labels,
					'description'         => __( 'EN Forms', 'planet4-gutenberg-engagingnetworks' ),
					'rewrite'             => false,
					'query_var'           => false,
					'public'              => false,
					'publicly_queryable'  => false,
					'capability_type'     => 'page',
					'has_archive'         => true,
					'hierarchical'        => false,
					'menu_position'       => null,
					'exclude_from_search' => true,
					'map_meta_cap'        => true,
					// necessary in order to use WordPress default custom post type list page.
					'show_ui'             => true,
					// hide it from menu, as we are using custom submenu pages.
					'show_in_menu'        => false,
					'supports'            => [ 'title' ],
				]
			);

			$custom_meta_args = [
				'type'   => 'string',
				'single' => true,
			];
			register_meta( self::POST_TYPE, self::FIELDS_META, $custom_meta_args );
		}

		/**
		 * Filter for post_row_actions. Alters edit action link and removes Quick edit action.
		 *
		 * @param string[] $actions An array of row action links. Defaults are
		 *                          'Edit', 'Quick Edit', 'Restore', 'Trash',
		 *                          'Delete Permanently', 'Preview', and 'View'.
		 * @param \WP_Post $post The post object.
		 *
		 * @return array  The filtered actions array.
		 */
		public function modify_post_row_actions( $actions, $post ) {

			// Check if post is of p4en_form_post type.
			if ( self::POST_TYPE === $post->post_type ) {
				/*
				 * Hide Quick Edit.
				 */
				$custom_actions = [
					'inline hide-if-no-js' => '',
				];

				$actions = array_merge( $actions, $custom_actions );
			}

			return $actions;
		}

		/**
		 * Adds shortcode for this custom post type.
		 *
		 * @param array $atts Array of attributes for the shortcode.
		 */
		public function handle_form_shortcode( $atts ) {
			global $pagenow;

			// Define attributes and their defaults.
			$atts = array_merge(
				[
					'id'            => 'id',
					'en_form_style' => 'full-width',
				],
				$atts
			);

			$post_id = filter_input( INPUT_GET, 'post', FILTER_VALIDATE_INT );

			if ( ! is_admin() ||
				( 'post.php' === $pagenow && $post_id && self::POST_TYPE === get_post_type( $post_id ) ) ||
				( 'admin-ajax.php' === $pagenow && self::POST_TYPE === get_post_type( $atts['id'] ) ) ) {

				$fields = get_post_meta( $atts['id'], self::FIELDS_META, true );

				$data = [
					'form_fields'   => $fields,
					'en_form_style' => $atts['en_form_style'],
				];

				$this->view->enform_post( $data );
			}
		}

		/**
		 * Creates a Meta box for the Selected Components of the current EN Form.
		 *
		 * @param \WP_Post $post The currently Added/Edited EN Form.
		 */
		public function add_form_meta_box( $post ) {
			add_meta_box(
				'meta-box-form',
				__( 'Form preview', 'planet4-gutenberg-engagingnetworks' ),
				[ $this, 'view_meta_box_form' ],
				[ self::POST_TYPE ],
				'normal',
				'high',
				$post
			);
		}

		/**
		 * View an EN form.
		 *
		 * @param \WP_Post $post The currently Added/Edited EN Form.
		 */
		public function view_meta_box_form( $post ) {
			echo do_shortcode( '[' . self::POST_TYPE . ' id="' . $post->ID . '" /]' );
		}

		/**
		 * Creates a Meta box for the Selected Components of the current EN Form.
		 *
		 * @param \WP_Post $post The currently Added/Edited EN Form.
		 */
		public function add_selected_meta_box( $post ) {
			add_meta_box(
				'meta-box-selected',
				__( 'Selected Components', 'planet4-gutenberg-engagingnetworks' ),
				[ $this, 'view_selected_meta_box' ],
				[ self::POST_TYPE ],
				'normal',
				'high',
				$post
			);
		}

		/**
		 * Prepares data to render the Selected Components meta box.
		 *
		 * @param \WP_Post $post The currently Added/Edited EN Form.
		 */
		public function view_selected_meta_box( $post ) {
			$form_fields = get_post_meta( $post->ID, self::FIELDS_META, true );
			$this->view->selected_meta_box(
				[
					'fields' => wp_json_encode( $form_fields ),
				]
			);
		}

		/**
		 * Adds available fields custom meta box to p4en_form edit post page.
		 *
		 * @param \WP_Post $post The currently Added/Edited EN Form.
		 */
		public function add_fields_meta_box( $post ) {
			add_meta_box(
				'fields_list_box',
				__( 'Available Fields', 'planet4-gutenberg-engagingnetworks' ),
				[ $this, 'display_fields_custom_box' ],
				self::POST_TYPE,
				'normal',
				'high',
				$post
			);
		}

		/**
		 * Display fields custom box content.
		 */
		public function display_fields_custom_box() {
			$list_table = new Enform_Fields_List_Table();
			$list_table->prepare_items();
			$list_table->display();
		}

		/**
		 * Adds a meta box for the EN questions.
		 *
		 * Adds available questions custom meta box to p4en_form edit post page.
		 */
		public function add_questions_custom_box() {
			add_meta_box(
				'questions_list_box',
				__( 'Available Questions', 'planet4-gutenberg-engagingnetworks' ),
				[ $this, 'display_questions_custom_box' ],
				self::POST_TYPE
			);
		}

		/**
		 * Display questions custom box content.
		 */
		public function display_questions_custom_box() {
			$list_table = new Enform_Questions_List_Table( 'GEN' );
			$list_table->prepare_items();
			$list_table->display();
		}

		/**
		 * Adds available opt-ins custom meta box to p4en_form edit post page.
		 */
		public function add_optins_custom_box() {
			add_meta_box(
				'optins_list_box',
				__( 'Available Opt-ins', 'planet4-gutenberg-engagingnetworks' ),
				[ $this, 'display_optins_custom_box' ],
				self::POST_TYPE
			);
		}

		/**
		 * Display opt-ins custom box content.
		 */
		public function display_optins_custom_box() {
			$list_table = new Enform_Questions_List_Table( 'OPT' );
			$list_table->prepare_items();
			$list_table->display();
		}

		/**
		 * Retrieves data of a specific question/opt-in.
		 */
		public function get_supporter_question_by_id() {
			// If this is an ajax call.
			if ( wp_doing_ajax() ) {
				$id                = filter_input( INPUT_GET, 'id', FILTER_SANITIZE_NUMBER_INT );
				$main_settings     = get_option( 'p4en_main_settings' );
				$ens_private_token = $main_settings['p4en_private_api'];
				$ens_api           = new Ensapi( $ens_private_token );
				$response          = $ens_api->get_supporter_question_by_id( $id );

				wp_send_json( $response );
			}
		}

		/**
		 * Add underscore templates to footer.
		 */
		public function print_admin_footer_scripts() {
			$this->view->view_template( 'selected_enform_fields', [] );
		}

		/**
		 * Hook load new page assets conditionally based on current page.
		 */
		public function load_assets() {
			global $pagenow, $typenow;
			$pages = [
				'post.php',
				'post-new.php',
			];

			// Load assets conditionally using pagenow, typenow on new/edit form page.
			if ( in_array( $pagenow, $pages, true ) && self::POST_TYPE === $typenow ) {
				add_action( "load-$pagenow", [ $this, 'load__new_page_assets' ] );
				add_action( 'admin_print_footer_scripts', [ $this, 'print_admin_footer_scripts' ], 1 );
			}
		}

		/**
		 * Load assets for new/edit form page.
		 */
		public function load__new_page_assets() {
			wp_enqueue_script( 'jquery-ui-core' );
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'jquery-ui-dialog' );
			wp_enqueue_script( 'jquery-ui-tooltip' );
			wp_enqueue_style( 'wp-jquery-ui-dialog' );
			wp_enqueue_script(
				'enforms',
				P4GEN_ADMIN_DIR . 'js/enforms.js',
				[
					'jquery',
					'wp-backbone',
				],
				'0.8.4',
				true
			);
		}

		/**
		 * Saves the p4 enform fields of the Post.
		 *
		 * @param int      $post_id The ID of the current Post.
		 * @param \WP_Post $post The current Post.
		 */
		public function save_fields_meta_box( $post_id, $post ) {
			global $pagenow;

			// Ignore autosave.
			if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
				return;
			}

			// Check user's capabilities.
			if ( ! current_user_can( 'edit_post', $post_id ) ) {
				return;
			}

			// Check post input.
			$form_fields = filter_input(
				INPUT_POST,
				self::FIELDS_META
			);

			// If this is a new post then set form fields meta.
			if ( $form_fields && 'post.php' === $pagenow ) {
				$form_fields = json_decode( ( $form_fields ) );

				// Store form fields meta.
				update_post_meta( $post_id, self::FIELDS_META, $form_fields );
			}
		}

		/**
		 * Validates the user input.
		 *
		 * @param array $settings The associative array with the input that the user submitted.
		 *
		 * @return bool
		 */
		public function validate( $settings ) : bool {
			return true;
		}

		/**
		 * Sanitizes the user input.
		 *
		 * @param array $input The associative array with the input that the user submitted.
		 */
		public function sanitize( &$input ) {}
	}
}
