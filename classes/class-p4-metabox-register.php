<?php
/**
 * P4 Metabox Register Class
 *
 * @package P4MT
 */

/**
 * Class P4_Metabox_Register
 */
class P4_Metabox_Register {

	/**
	 * The maximum number of take action pages to show in dropdown.
	 *
	 * @const int MAX_TAKE_ACTION_PAGES
	 */
	const MAX_TAKE_ACTION_PAGES = 100;

	/**
	 * P4_Metabox_Register constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Class hooks.
	 */
	private function hooks() {
		add_action( 'cmb2_admin_init', [ $this, 'register_p4_meta_box' ] );
		add_action( 'cmb2_after_form', [ $this, 'cmb2_after_form_do_js_validation' ], 10, 2 );
	}

	/**
	 * Register P4 meta box.
	 */
	public function register_p4_meta_box() {
		$this->register_meta_box_header();
		$this->register_meta_box_post();
		$this->register_meta_box_open_graph();
		$this->register_meta_box_campaign();
	}

	/**
	 * Register Page Header meta box.
	 */
	public function register_meta_box_header() {

		$p4_header = new_cmb2_box(
			[
				'id'           => 'p4_metabox',
				'title'        => __( 'Page Header Fields', 'planet4-master-theme-backend' ),
				'object_types' => [ 'page', 'campaign' ], // Post type.
			]
		);

		$p4_header->add_field(
			[
				'name' => __( 'Header Title', 'planet4-master-theme-backend' ),
				'desc' => __( 'Header title comes here', 'planet4-master-theme-backend' ),
				'id'   => 'p4_title',
				'type' => 'text_medium',
			]
		);

		$p4_header->add_field(
			[
				'name' => __( 'Header Subtitle', 'planet4-master-theme-backend' ),
				'desc' => __( 'Header subtitle comes here', 'planet4-master-theme-backend' ),
				'id'   => 'p4_subtitle',
				'type' => 'text_medium',
			]
		);

		$p4_header->add_field(
			[
				'name'    => __( 'Header Description', 'planet4-master-theme-backend' ),
				'desc'    => __( 'Header description comes here', 'planet4-master-theme-backend' ),
				'id'      => 'p4_description',
				'type'    => 'wysiwyg',
				'options' => [
					'textarea_rows' => 5,
					'media_buttons' => false,
				],
			]
		);

		$p4_header->add_field(
			[
				'name'       => __( 'Header Button Title', 'planet4-master-theme-backend' ),
				'desc'       => __( 'Header button title comes here', 'planet4-master-theme-backend' ),
				'id'         => 'p4_button_title',
				'type'       => 'text_medium',
				'show_on_cb' => [ $this, 'is_not_campaign_post' ],
			]
		);

		$p4_header->add_field(
			[
				'name'       => __( 'Header Button Link', 'planet4-master-theme-backend' ),
				'desc'       => __( 'Header button link comes here', 'planet4-master-theme-backend' ),
				'id'         => 'p4_button_link',
				'type'       => 'text_medium',
				'show_on_cb' => [ $this, 'is_not_campaign_post' ],
			]
		);

		$p4_header->add_field(
			[
				'name'       => __( 'New Tab', 'planet4-master-theme-backend' ),
				'desc'       => __( 'Open header button link in new tab', 'planet4-master-theme-backend' ),
				'id'         => 'p4_button_link_checkbox',
				'type'       => 'checkbox',
				'show_on_cb' => [ $this, 'is_not_campaign_post' ],
			]
		);

		$p4_header->add_field(
			[
				'name'         => __( 'Background override', 'planet4-master-theme-backend' ),
				'desc'         => __( 'Upload an image', 'planet4-master-theme-backend' ),
				'id'           => 'background_image',
				'type'         => 'file',
				// Optional.
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => __( 'Add Background Image', 'planet4-master-theme-backend' ),
				],
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'large',
			]
		);

		$p4_header->add_field(
			[
				'name' => __( 'Hide page title', 'planet4-master-theme-backend' ),
				'desc' => __( 'Hide page title on frontend page.', 'planet4-master-theme-backend' ),
				'id'   => 'p4_hide_page_title_checkbox',
				'type' => 'checkbox',
			]
		);
	}

	/**
	 * Register Post meta box.
	 */
	public function register_meta_box_post() {

		$p4_post = new_cmb2_box(
			[
				'id'           => 'p4_metabox_post',
				'title'        => __( 'Post Articles Element Fields', 'planet4-master-theme-backend' ),
				'object_types' => [ 'post' ],
			]
		);

		$p4_post->add_field(
			[
				'name' => __( 'Author Override', 'planet4-master-theme-backend' ),
				'desc' => __( 'Enter author name if you want to override the author', 'planet4-master-theme-backend' ),
				'id'   => 'p4_author_override',
				'type' => 'text_medium',
			]
		);

		$p4_post->add_field(
			[
				'name'             => __( 'Take Action Page Selector', 'planet4-master-theme-backend' ),
				'desc'             => __( 'Select a Take Action Page to populate take action boxout block', 'planet4-master-theme-backend' ),
				'id'               => 'p4_take_action_page',
				'type'             => 'select',
				'show_option_none' => true,
				'options_cb'       => [ $this, 'populate_act_page_children_options' ],
			]
		);

		$p4_post->add_field(
			[
				'name'    => __( 'Include Articles In Post', 'planet4-master-theme-backend' ),
				'id'      => 'include_articles',
				'type'    => 'select',
				'options' => [
					'yes' => 'Yes',
					'no'  => 'No',
				],
			]
		);

		$p4_post->add_field(
			[
				'name'         => __( 'Background Image Override', 'planet4-master-theme-backend' ),
				'desc'         => __( 'Upload an image or select one from the media library to override the background image', 'planet4-master-theme-backend' ),
				'id'           => 'p4_background_image_override',
				'type'         => 'file',
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => __( 'Add Image', 'planet4-master-theme-backend' ),
				],
				'preview_size' => 'large',
			]
		);
	}

	/**
	 * Register Open Graph meta box.
	 */
	public function register_meta_box_open_graph() {

		$p4_open_graph = new_cmb2_box(
			[
				'id'           => 'p4_metabox_og',
				'title'        => __( 'Open Graph/Social Fields', 'planet4-master-theme-backend' ),
				'object_types' => [ 'page', 'post', 'campaign' ],
				'closed'       => true,  // Keep the metabox closed by default.
			]
		);

		$p4_open_graph->add_field(
			[
				'name' => __( 'Title', 'planet4-master-theme-backend' ),
				'desc' => __( 'Enter title if you want to override the open graph title', 'planet4-master-theme-backend' ),
				'id'   => 'p4_og_title',
				'type' => 'text_medium',
			]
		);

		$p4_open_graph->add_field(
			[
				'name'    => __( 'Description', 'planet4-master-theme-backend' ),
				'desc'    => __( 'Enter description if you want to override the open graph description', 'planet4-master-theme-backend' ),
				'id'      => 'p4_og_description',
				'type'    => 'wysiwyg',
				'options' => [
					'media_buttons' => false,
					'textarea_rows' => 5,
				],
			]
		);

		$p4_open_graph->add_field(
			[
				'name'         => __( 'Image Override', 'planet4-master-theme-backend' ),
				'desc'         => __( 'Upload an image or select one from the media library to override the open graph image', 'planet4-master-theme-backend' ),
				'id'           => 'p4_og_image',
				'type'         => 'file',
				'options'      => [
					'url' => false,
				],
				'text'         => [
					'add_upload_file_text' => __( 'Add Image', 'planet4-master-theme-backend' ),
				],
				'query_args'   => [
					'type' => 'image',
				],
				'preview_size' => 'large',
			]
		);
	}

	/**
	 * Register Campaign Information meta box.
	 */
	public function register_meta_box_campaign() {
		$post_types       = [ 'page', 'campaign', 'post' ];
		$analytics_values = P4_Analytics_Values::from_cache_or_api_or_hardcoded();

		// P4 Datalayer/Campaign fields.
		$p4_campaign_fields = new_cmb2_box(
			[
				'id'           => 'p4_campaign_fields',
				'title'        => __( 'Analytics & Tracking', 'planet4-master-theme-backend' ),
				'object_types' => $post_types, // Post type.
				'closed'       => true,  // Keep the metabox closed by default.
				'context'      => 'side', // show cmb2box in right sidebar.
				'priority'     => 'low',
				'show_names'   => false, // Hide the labels.
			]
		);

		$global_project_options = self::maybe_add_current_post_value( $analytics_values->global_projects_options(), 'p4_campaign_name' );

		$p4_campaign_fields->add_field(
			[
				'name'       => __( 'Campaign Name', 'planet4-master-theme-backend' ),
				'id'         => 'p4_campaign_name',
				'type'       => 'select',
				'options'    => $global_project_options,
				'attributes' => [
					'data-validation' => 'required',
				],
			]
		);

		$local_projects_options = self::maybe_add_current_post_value( $analytics_values->local_projects_options(), 'p4_local_project' );
		$p4_campaign_fields->add_field(
			[
				'name'    => __( 'Local Projects', 'planet4-master-theme-backend' ),
				'id'      => 'p4_local_project',
				'type'    => 'select',
				'options' => $local_projects_options,
			]
		);

		$basket_options = [
			'not set'                  => __( '- Select Basket -', 'planet4-master-theme-backend' ),
			'Forests'                  => 'Forests',
			'Oceans'                   => 'Oceans',
			'Good Life'                => 'Good Life',
			'Food'                     => 'Food',
			'Climate &amp; Energy'     => 'Climate & Energy',
			'Oil'                      => 'Oil',
			'Plastics'                 => 'Plastics',
			'Political &amp; Business' => 'Political & Business',
		];

		$p4_campaign_fields->add_field(
			[
				'name'       => __( 'Basket Name', 'planet4-master-theme-backend' ),
				'id'         => 'p4_basket_name',
				'type'       => 'select',
				'options'    => $basket_options,
				'attributes' => [
					'data-validation' => 'required',
				],
			]
		);

		$p4_campaign_fields->add_field(
			[
				'name'       => __( 'Department', 'planet4-master-theme-backend' ),
				'id'         => 'p4_department',
				'type'       => 'text_medium',
				'attributes' => [
					'placeholder' => __( 'Add Department', 'planet4-master-theme-backend' ),
				],
			]
		);
	}

	/**
	 * Populate an associative array with all the children of the ACT page
	 *
	 * @return array
	 */
	public function populate_act_page_children_options() {
		$parent_act_id = planet4_get_option( 'act_page' );
		$options       = [];

		if ( 0 !== absint( $parent_act_id ) ) {
			$take_action_pages_args = [
				'post_type'        => 'page',
				'post_parent'      => $parent_act_id,
				'post_status'      => 'publish',
				'orderby'          => 'post_title',
				'order'            => 'ASC',
				'suppress_filters' => false,
				'numberposts'      => self::MAX_TAKE_ACTION_PAGES,
			];

			$posts = get_posts( $take_action_pages_args );
			foreach ( $posts as $post ) {
				$options[ $post->ID ] = $post->post_title;
			}
		}

		return $options;
	}

	/**
	 * Validate CMB2 fields that have the 'data-validation' attribute set to 'required'.
	 *
	 * @param int  $post_id The ID of the current Post.
	 * @param CMB2 $cmb The CMB2 object.
	 */
	public function cmb2_after_form_do_js_validation( $post_id, $cmb ) {
		static $added = false;

		// Only add this to the page once (not for every metabox).
		if ( $added ) {
			return;
		}

		$added = true;
		wp_enqueue_script( 'cmb2_form_validation', get_template_directory_uri() . '/admin/js/cmb2_form_validation.js', [], '0.1', true );
	}

	/**
	 * Checks if current post is not of campaign post type.
	 *
	 * @return bool
	 */
	public function is_not_campaign_post() {
		return P4_Post_Campaign::POST_TYPE !== get_post_type();
	}

	/**
	 * Look up the ID of the global campaign and save it on the post.
	 *
	 * @param bool       $updated Whether the field is being updated.
	 * @param string     $action The action being performed on the field.
	 * @param CMB2_Field $field The field being updated.
	 */
	public static function save_global_project_id( $updated, $action, CMB2_Field $field ) {
		if ( ! $updated ) {
			return;
		}
		if ( 'removed' === $action ) {
			update_post_meta( $field->object_id(), 'p4_global_project_tracking_id', null );

			return;
		}

		$project_id = P4_Analytics_Values::from_cache_or_api_or_hardcoded()->get_id_for_global_project( $field->value() );
		update_post_meta( $field->object_id, 'p4_global_project_tracking_id', $project_id );
	}

	/**
	 * If the post has a value that is not in the sheet, keep it in the dropdown so that metaboxes save doesn't set
	 * it to another value, but mark it with `[DEPRECATED]` prefix.
	 *
	 * @param array  $options_array The list of supported global/local projects.
	 * @param string $field_name The meta field name.
	 * @return array The list with maybe the current post value.
	 */
	private static function maybe_add_current_post_value( array $options_array, $field_name ): array {
		// Not pretty but will work for now.
		// phpcs:disable WordPress.Security.NonceVerification.Recommended
		$post_id = $_GET['post'] ?? null;
		if ( ! $post_id ) {
			return $options_array;
		}

		$current_post_meta_value = get_post_meta( $post_id, $field_name );

		if (
			isset( $current_post_meta_value[0] )
			&& ! ( array_key_exists(
				$current_post_meta_value[0],
				$options_array
			) )
		) {
			$options_array = [ $current_post_meta_value[0] => __( '[DEPRECATED] ', 'planet4-master-theme-backend' ) . $current_post_meta_value[0] ] + $options_array;
		}

		return $options_array;
	}
}
