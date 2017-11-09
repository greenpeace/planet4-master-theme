<?php

if ( ! class_exists( 'Timber' ) ) {
	add_action(
		'admin_notices', function() {
			printf( '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="%s">Plugins menu</a></p></div>',
				esc_url( admin_url( 'plugins.php#timber' ) )
			);
		}
	);

	add_filter(
		'template_include', function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);

	return;
}

/**
 * Class P4_Master_Site.
 * The main class that handles Planet4 Master Theme.
 */
class P4_Master_Site extends TimberSite {

	/** @var string $theme_dir */
	protected $theme_dir;
	/** @var string $theme_images_dir */
	protected $theme_images_dir;
	/** @var array $websites */
	protected $websites = [
		'en_US' => 'International (English)',
		'el_GR' => 'Greece (Ελληνικά)',
	];
	/** @var string $default_sort */
	protected $default_sort;
	/** @var int $posts_per_page */
	protected $posts_per_page;
	/** @var array $services */
	protected $services;
	/** @var array $child_css */
	protected $child_css = array();

	/**
	 * P4_Master_Site constructor.
	 *
	 * @param array $services The dependencies to inject.
	 */
	public function __construct( $services = array() ) {

		$this->load();
		$this->settings();
		$this->hooks();
		$this->services( $services );

		parent::__construct();
	}

	/**
	 * Load required files.
	 */
	protected function load() {
		/**
		 * Class names need to be prefixed with P4 and should use capitalized words separated by underscores.
		 * Any acronyms should be all upper case.
		 */
		spl_autoload_register(
			function ( $class_name ) {
				if ( strpos( $class_name, 'P4' ) !== false ) {
					$file_name = 'class-' . str_ireplace( [ 'P4\\', '_' ], [ '', '-' ], strtolower( $class_name ) );
					require_once 'classes/' . $file_name . '.php';
				}
			}
		);
	}

	/**
	 * Define settings for the Planet4 Master Theme.
	 */
	protected function settings() {
		Timber::$autoescape     = true;
		Timber::$dirname        = [ 'templates', 'views' ];
		$this->theme_dir        = get_template_directory_uri();
		$this->theme_images_dir = $this->theme_dir . '/images/';
		$this->default_sort     = 'relevant';
		$this->posts_per_page   = 10;
	}

	/**
	 * Hooks the theme.
	 */
	protected function hooks() {
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_post_type_support( 'page', 'excerpt' );  // Added excerpt option to pages.

		add_filter( 'timber_context',         array( $this, 'add_to_context' ) );
		add_filter( 'get_twig',               array( $this, 'add_to_twig' ) );
		add_action( 'init',                   array( $this, 'register_post_types' ) );
		add_action( 'init',                   array( $this, 'register_taxonomies' ) );
		add_action( 'pre_get_posts',          array( $this, 'add_search_options' ) );
		add_filter( 'searchwp_query_orderby', array( $this, 'edit_searchwp_query_orderby' ), 10, 2 );
		add_action( 'cmb2_admin_init',        array( $this, 'register_header_metabox' ) );
		add_action( 'pre_get_posts',          array( $this, 'tags_support_query' ) );
		add_action( 'admin_init',             array( $this, 'add_copyright_text' ) );
		add_action( 'admin_init',             array( $this, 'add_google_tag_manager_identifier_setting' ) );
    add_action( 'admin_init',             array( $this, 'add_engaging_network_form_id' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts',     array( $this, 'enqueue_public_assets' ) );

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		register_nav_menus( array(
			'navigation-bar-menu' => __( 'Navigation Bar Menu', 'planet4-master-theme' )
		) );
	}

	/**
	 * Inject dependencies.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function services( $services = array() ) {
		$this->services = $services;
		if ( $this->services ) {
			foreach ( $this->services as $service ) {
				new $service();
			}
		}
	}

	/**
	 * Show copyright text field.
	 *
	 * @param array $args
	 */
	public function copyright_show_settings( $args ) {
		$copyright = get_option( 'copyright', '' );

		printf(
			'<input type="text" name="copyright" class="regular-text" value="%1$s" id="%2$s" />',
			esc_attr( $copyright ),
			esc_attr( $args['label_for'] )
		);
	}

	/**
	 * Show google tag manager identifier text field.
	 *
	 * @param array $args
	 */
	public function google_tag_show_settings( $args ) {
		$google_tag_identifier = get_option( 'google_tag_manager_identifier', '' );

		printf(
			'<input type="text" name="google_tag_manager_identifier" class="regular-text" value="%1$s" id="%2$s" />',
			esc_attr( $google_tag_identifier ),
			esc_attr( $args['label_for'] )
		);
	}

	/**
	 * Show Engaging network id text field.
	 *
	 * @param array $args
	 */
	public function engaging_network_id_show_settings( $args ) {
		$engaging_network_id = get_option( 'engaging_network_form_id', '' );

		printf(
			'<input type="text" name="engaging_network_form_id" class="regular-text" value="%1$s" id="%2$s" />',
			esc_attr( $engaging_network_id ),
			esc_attr( $args['label_for'] )
		);
	}

	/**
	 * Function to add copyright text block in general options
	 */
	public function add_copyright_text() {
		add_settings_section(
			'copyrighttext_id',
			'',
			'',
			'general'
		);

		// Register taxonomies for page.
		register_setting(
			'general',
			'copyright',
			'trim'
		);

		// Register the field for the "copyright" section.
		add_settings_field(
			'copyright',
			'Copyright Text',
			array( $this, 'copyright_show_settings' ),
			'general',
			'copyrighttext_id',
			array(
				'label_for' => 'copyrighttext_id',
			)
		);
	}


	/**
	 * Function to add google tag manager identifier block in general options
	 */
	public function add_google_tag_manager_identifier_setting() {

		// Add google tag manager identifier section.
		add_settings_section(
			'google_tag_manager_identifier',
			'',
			'',
			'general'
		);

		// Register google tag manager identifier setting.
		register_setting(
			'general',
			'google_tag_manager_identifier',
			'trim'
		);

		// Register the field for the "google tag manager identifier" section.
		add_settings_field(
			'google_tag_manager_identifier',
			'Google Tag Manager Identifier',
			array( $this, 'google_tag_show_settings' ),
			'general',
			'google_tag_manager_identifier',
			array(
				'label_for' => 'google_tag_manager_identifier',
			)
		);
	}

/**
	 * Function to add engaging network ID option in general options
	 */
	public function add_engaging_network_form_id() {
		add_settings_section(
			'engaging_network_form_id',
			'',
			'',
			'general'
		);

		// Register taxonomies for page.
		register_setting(
			'general',
			'engaging_network_form_id',
			'trim'
		);

		// Register the field for the "copyright" section.
		add_settings_field(
			'engaging_network_id',
			'Engaging Network ID',
			array( $this, 'engaging_network_id_show_settings' ),
			'general',
			'engaging_network_form_id',
			array(
				'label_for' => 'engaging_network_form_id',
			)
		);
	}

	/**
	 * Load styling and behaviour on admin pages.
	 */
	public function enqueue_admin_assets() {
		// Register jQuery 3 for use wherever needed by adding wp_enqueue_script( 'jquery-3' );.
		wp_register_script( 'jquery-3', 'https://code.jquery.com/jquery-3.2.1.min.js', array(), '3.2.1', true );
	}

	/**
	 * Load styling and behaviour on website pages.
	 */
	public function enqueue_public_assets() {
		wp_enqueue_style( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css', array(), '4.0.0-alpha.6' );
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/style.css', [], '0.0.2'  );
		wp_register_script( 'jquery-3', 'https://code.jquery.com/jquery-3.2.1.min.js', array(), '3.2.1', true );
		wp_enqueue_script( 'popperjs', $this->theme_dir . '/assets/js/popper.min.js', array(), '1.11.0', true );
		wp_enqueue_script( 'bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js', array(), '4.0.0-beta', true );
		wp_enqueue_script( 'main', $this->theme_dir . '/assets/js/main.js', array( 'jquery' ), '0.0.2', true );
	}

	/**
	 * Registers custom post types.
	 */
	public function register_post_types() {}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
		register_taxonomy_for_object_type( 'post_tag', 'page' );
		register_taxonomy_for_object_type( 'category', 'page' );
	}

	/**
	 * Include tags and categories when querying.
	 *
	 * @param WP_Query $wp_query The WP_Query object.
	 */
	public function tags_support_query( $wp_query ) {
		if ( $wp_query->get( 'tag' ) ) {
			$wp_query->set( 'post_type', 'any' );
		}

		if ( $wp_query->get( 'category_name' ) ) {
			$wp_query->set( 'post_type', 'any' );
		}
	}

	/**
	 * Add custom options to the main WP_Query.
	 *
	 * @param WP_Query $wp The WP Query to customize.
	 */
	public function add_search_options( WP_Query $wp ) {
		if ( ! $wp->is_main_query() || ! $wp->is_search() ) {
			return;
		}
		$wp->set( 'posts_per_page', $this->posts_per_page );
	}

	/**
	 * Customize the order of search results.
	 *
	 * @param string $sql The part of the query related to the ORDER BY.
	 *
	 * @return string The customized part of the query related to the ORDER BY.
	 */
	function edit_searchwp_query_orderby( $sql ) {
		global $wp_query;

		$selected_sort  = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$selected_order = $wp_query->get( 'order' );

		if ( $selected_sort !== $this->default_sort ) {
			return esc_sql( sprintf( 'ORDER BY %s %s', $selected_sort, $selected_order ) );
		}
		return esc_sql( $sql );
	}

	/**
	 * Populate an associative array with all the children of the ACT page
	 *
	 * @return array
	 */
	public function populate_act_page_children_options() {

		// Get the id of the ACT page. We need this to get the children posts/pages of the ACT Page.
		$arguments = [
			'post_type'     => 'page',
			'post_name__in' => [ 'act', 'ACT', 'Act' ],
		];

		$query_act_page = new WP_Query( $arguments );
		$options        = [];

		// If ACT Page is found construct arguments array for the select box.
		if ( $query_act_page->have_posts() ) {
			$act_pages              = $query_act_page->get_posts();
			$act_page               = $act_pages[0];
			$take_action_pages_args = [
				'post_type'   => 'page',
				'post_parent' => $act_page->ID,
			];

			$query_children = new WP_Query( $take_action_pages_args );
			$posts          = $query_children->get_posts();
			foreach ( $posts as $post ) {
				$options[ $post->ID ] = $post->post_title;
			}
		}

		return $options;
	}

	/**
	 * Hook in and add a Theme metabox. Can only happen on the 'cmb2_admin_init' or 'cmb2_init' hook.
	 */
	public function register_header_metabox() {

		$prefix = 'p4_';

		$p4_header = new_cmb2_box( array(
			'id'            => $prefix . 'metabox',
			'title'         => __( 'Page Header Fields', 'planet4-master-theme' ),
			'object_types'  => array( 'page' ), // Post type.
		) );

		$p4_header->add_field( array(
			'name' => __( 'Header Title', 'planet4-master-theme' ),
			'desc' => __( 'Header title comes here', 'planet4-master-theme' ),
			'id'   => $prefix . 'title',
			'type' => 'text_medium',
		) );

		$p4_header->add_field(
			array(
				'name' => __( 'Header Subtitle', 'planet4-master-theme' ),
				'desc' => __( 'Header subtitle comes here', 'planet4-master-theme' ),
				'id'   => $prefix . 'subtitle',
				'type' => 'text_medium',
			)
		);

		$p4_header->add_field(
			array(
				'name'    => __( 'Header Description', 'planet4-master-theme' ),
				'desc'    => __( 'Header description comes here', 'planet4-master-theme' ),
				'id'      => $prefix . 'description',
				'type'    => 'wysiwyg',
				'options' => array(
					'textarea_rows' => 5,
				),
			)
		);

		$p4_header->add_field(
			array(
				'name' => __( 'Header Button Title', 'planet4-master-theme' ),
				'desc' => __( 'Header button title comes here', 'planet4-master-theme' ),
				'id'   => $prefix . 'button_title',
				'type' => 'text_medium',
			)
		);

		$p4_header->add_field(
			array(
				'name' => __( 'Header Button Link', 'planet4-master-theme' ),
				'desc' => __( 'Header button link comes here', 'planet4-master-theme' ),
				'id'   => $prefix . 'button_link',
				'type' => 'text_medium',
			)
		);

		$p4_header->add_field(
			array(
				'name'    => __( 'Background overide', 'planet4-master-theme' ),
				'desc'    => __( 'Upload an image', 'planet4-master-theme' ),
				'id'      => 'background_image',
				'type'    => 'file',
				// Optional
				'options' => array(
					'url' => false,
				),
				'text'    => array(
					'add_upload_file_text' => __( 'Add Background Image', 'planet4-master-theme' )
				),
				'query_args' => array(
					'type' => 'image',
				),
				'preview_size' => 'large',
			)
		);

		$p4_post = new_cmb2_box( [
			'id'           => $prefix . 'metabox_post',
			'title'        => __( 'Post Articles Element Fields', 'planet4-master-theme' ),
			'object_types' => [ 'post' ],
		] );

		$p4_post->add_field( [
			'name' => __( 'Articles Title', 'planet4-master-theme' ),
			'desc' => __( 'Title for articles block', 'planet4-master-theme' ),
			'id'   => $prefix . 'articles_title',
			'type' => 'text_medium',
		] );

		$p4_post->add_field( [
			'name'       => __( 'Articles Count', 'planet4-master-theme' ),
			'desc'       => __( 'Number of articles that should be displayed for articles block', 'planet4-master-theme' ),
			'id'         => $prefix . 'articles_count',
			'type'       => 'text_medium',
			'attributes' => [
				'type' => 'number',
			],
		] );

		$p4_post->add_field( [
			'name' => __( 'Author Override', 'planet4-master-theme' ),
			'desc' => __( 'Enter author name if you want to override the author', 'planet4-master-theme' ),
			'id'   => $prefix . 'author_override',
			'type' => 'text_medium',
		] );

		$p4_post->add_field( [
			'name'             => __( 'Take Action Page Selector', 'planet4-master-theme' ),
			'desc'             => __( 'Select a Take Action Page to populate take action boxout block', 'planet4-master-theme' ),
			'id'               => $prefix . 'take_action_page',
			'type'             => 'select',
			'show_option_none' => true,
			'options_cb'       => [ $this, 'populate_act_page_children_options' ],
		] );

		$p4_post->add_field( [
			'name'         => __( 'Background Image Override', 'planet4-master-theme' ),
			'desc'         => __( 'Upload an image or select one from the media library to override the background image', 'planet4-master-theme' ),
			'id'           => $prefix . 'background_image_override',
			'type'         => 'file',
			'options'      => [
				'url' => false,
			],
			'text'         => [
				'add_upload_file_text' => __( 'Add Image', 'planet4-master-theme' ),
			],
			'preview_size' => 'large',
		] );
	}

	/**
	 * Adds more data to the context variable that will be passed to the main template.
	 *
	 * @param array $context The associative array with data to be passed to the main template.
	 *
	 * @return mixed
	 */
	public function add_to_context( $context ) {
		$context['data_nav_bar'] = [
			'websites'     => $this->websites,
			'images'       => $this->theme_images_dir,
			'home_url'     => home_url( '/' ),
			'act_url'      => '/act',
			'explore_url'  => '/explore',
			'search_query' => get_search_query(),
		];
		$context['foo']  = 'bar';   // For unit test purposes.
		$context['domain'] = 'planet4-master-theme';
		$context['site'] = $this;
		$context['navbar_menu'] = new TimberMenu('navigation-bar-menu');
		return $context;
	}

	/**
	 * Add your own functions to Twig.
	 *
	 * @param Twig_ExtensionInterface $twig The Twig object that implements the Twig_ExtensionInterface.
	 *
	 * @return mixed
	 */
	public function add_to_twig( $twig ) {
		$twig->addExtension( new Twig_Extension_StringLoader() );
		return $twig;
	}
}

new P4_Master_Site( [
	'P4_Taxonomy_Image',
] );
