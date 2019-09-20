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
	 * Meta box prefix.
	 *
	 * @var string $prefix
	 */
	private $prefix = 'p4_';

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
				'id'           => $this->prefix . 'metabox',
				'title'        => __( 'Page Header Fields', 'planet4-master-theme-backend' ),
				'object_types' => [ 'page', 'campaign' ], // Post type.
			]
		);

		$p4_header->add_field(
			[
				'name' => __( 'Header Title', 'planet4-master-theme-backend' ),
				'desc' => __( 'Header title comes here', 'planet4-master-theme-backend' ),
				'id'   => $this->prefix . 'title',
				'type' => 'text_medium',
			]
		);

		$p4_header->add_field(
			[
				'name' => __( 'Header Subtitle', 'planet4-master-theme-backend' ),
				'desc' => __( 'Header subtitle comes here', 'planet4-master-theme-backend' ),
				'id'   => $this->prefix . 'subtitle',
				'type' => 'text_medium',
			]
		);

		$p4_header->add_field(
			[
				'name'    => __( 'Header Description', 'planet4-master-theme-backend' ),
				'desc'    => __( 'Header description comes here', 'planet4-master-theme-backend' ),
				'id'      => $this->prefix . 'description',
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
				'id'         => $this->prefix . 'button_title',
				'type'       => 'text_medium',
				'show_on_cb' => [ $this, 'is_not_campaign_post' ],
			]
		);

		$p4_header->add_field(
			[
				'name'       => __( 'Header Button Link', 'planet4-master-theme-backend' ),
				'desc'       => __( 'Header button link comes here', 'planet4-master-theme-backend' ),
				'id'         => $this->prefix . 'button_link',
				'type'       => 'text_medium',
				'show_on_cb' => [ $this, 'is_not_campaign_post' ],
			]
		);

		$p4_header->add_field(
			[
				'name'       => __( 'New Tab', 'planet4-master-theme-backend' ),
				'desc'       => __( 'Open header button link in new tab', 'planet4-master-theme-backend' ),
				'id'         => $this->prefix . 'button_link_checkbox',
				'type'       => 'checkbox',
				'show_on_cb' => [ $this, 'is_not_campaign_post' ],
			]
		);

		$p4_header->add_field(
			[
				'name'         => __( 'Background overide', 'planet4-master-theme-backend' ),
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
				'name'       => __( 'Hide page title', 'planet4-master-theme-backend' ),
				'desc'       => __( 'Hide page title on frontend page.', 'planet4-master-theme-backend' ),
				'id'         => $this->prefix . 'hide_page_title_checkbox',
				'type'       => 'checkbox',
				'show_on_cb' => [ $this, 'is_not_campaign_post' ],
			]
		);
	}

	/**
	 * Register Post meta box.
	 */
	public function register_meta_box_post() {

		$p4_post = new_cmb2_box(
			[
				'id'           => $this->prefix . 'metabox_post',
				'title'        => __( 'Post Articles Element Fields', 'planet4-master-theme-backend' ),
				'object_types' => [ 'post' ],
			]
		);

		$p4_post->add_field(
			[
				'name' => __( 'Author Override', 'planet4-master-theme-backend' ),
				'desc' => __( 'Enter author name if you want to override the author', 'planet4-master-theme-backend' ),
				'id'   => $this->prefix . 'author_override',
				'type' => 'text_medium',
			]
		);

		$p4_post->add_field(
			[
				'name'             => __( 'Take Action Page Selector', 'planet4-master-theme-backend' ),
				'desc'             => __( 'Select a Take Action Page to populate take action boxout block', 'planet4-master-theme-backend' ),
				'id'               => $this->prefix . 'take_action_page',
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
				'id'           => $this->prefix . 'background_image_override',
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
				'id'           => $this->prefix . 'metabox_og',
				'title'        => __( 'Open Graph/Social Fields', 'planet4-master-theme-backend' ),
				'object_types' => [ 'page', 'post', 'campaign' ],
				'closed'       => true,  // Keep the metabox closed by default.
			]
		);

		$p4_open_graph->add_field(
			[
				'name' => __( 'Title', 'planet4-master-theme-backend' ),
				'desc' => __( 'Enter title if you want to override the open graph title', 'planet4-master-theme-backend' ),
				'id'   => $this->prefix . 'og_title',
				'type' => 'text_medium',
			]
		);

		$p4_open_graph->add_field(
			[
				'name'    => __( 'Description', 'planet4-master-theme-backend' ),
				'desc'    => __( 'Enter description if you want to override the open graph description', 'planet4-master-theme-backend' ),
				'id'      => $this->prefix . 'og_description',
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
				'id'           => $this->prefix . 'og_image',
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

		// P4 Datalayer/Campaign fields.
		$p4_campaign_fields = new_cmb2_box(
			[
				'id'           => $this->prefix . 'campaign_fields',
				'title'        => __( 'Campaign information (dataLayer)', 'planet4-master-theme-backend' ),
				'object_types' => [ 'page', 'campaign', 'post' ], // Post type.
				'closed'       => true,  // Keep the metabox closed by default.
				'context'      => 'side', // show cmb2box in right sidebar.
				'priority'     => 'low',
				'show_names'   => false, // Hide the labels.
			]
		);

		$campaign_options = [
			'not set'                            => __( '- Select Campaign -', 'planet4-master-theme-backend' ),
			'All Eyes on the Amazon'             => 'All Eyes on the Amazon',
			'Amazon Reef'                        => 'Amazon Reef',
			'Asia Energy Transition'             => 'Asia Energy Transition',
			'BrAndino: Hold the Line'            => 'BrAndino: Hold the Line',
			'Break Free'                         => 'Break Free',
			'Climate Emergency'                  => 'Climate Emergency',
			'Climate Emergency Response'         => 'Climate Emergency Response',
			'Climate Justice Liability'          => 'Climate Justice Liability',
			'Congo Basin Forests'                => 'Congo Basin Forests',
			'Corporate ICE/ Clean Air Now'       => 'Corporate ICE/ Clean Air Now',
			'Cross-commodities markets campaign' => 'Cross-commodities markets campaign',
			'Ends of the Earth'                  => 'Ends of the Earth',
			'European Energy Transition'         => 'European Energy Transition',
			'Greenpeace Fires'                   => 'Greenpeace Fires',
			'Indonesia Forests'                  => 'Indonesia Forests',
			'Local Campaign'                     => 'Local Campaign',
			'Meat &amp; Dairy'                   => 'Meat & Dairy',
			'Ocean Sanctuaries'                  => 'Ocean Sanctuaries',
			'Patagonia'                          => 'Patagonia',
			'People vs. Oil'                     => 'People vs. Oil',
			'Pipelines'                          => 'Pipelines',
			'Plastics Free Future'               => 'Plastics Free Future',
			'Shifting the trillions'             => 'Shifting the trillions',
			'Stolen Fish'                        => 'Stolen Fish',
			'The Future of Europe project'       => 'The Future of Europe project',
			'Urban Revolution'                   => 'Urban Revolution',
		];

		$p4_campaign_fields->add_field(
			[
				'name'       => __( 'Campaign Name', 'planet4-master-theme-backend' ),
				'id'         => $this->prefix . 'campaign_name',
				'type'       => 'select',
				'options'    => $campaign_options,
				'attributes' => [
					'data-validation' => 'required',
				],
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
			'Political &amp; Business' => 'Political & Business',
		];

		$p4_campaign_fields->add_field(
			[
				'name'       => __( 'Basket Name', 'planet4-master-theme-backend' ),
				'id'         => $this->prefix . 'basket_name',
				'type'       => 'select',
				'options'    => $basket_options,
				'attributes' => [
					'data-validation' => 'required',
				],
			]
		);

		$scope_options = [
			'not set'  => __( '- Select Scope -', 'planet4-master-theme-backend' ),
			'Global'   => 'Global',
			'National' => 'National',
		];

		$p4_campaign_fields->add_field(
			[
				'name'       => __( 'Scope', 'planet4-master-theme-backend' ),
				'id'         => $this->prefix . 'scope',
				'type'       => 'select',
				'options'    => $scope_options,
				'attributes' => [
					'data-validation' => 'required',
				],
			]
		);

		$p4_campaign_fields->add_field(
			[
				'name'       => __( 'Department', 'planet4-master-theme-backend' ),
				'id'         => $this->prefix . 'department',
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
}
