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
	/** @var array $child_css */
	protected $child_css = array();

	/**
	 * P4_Master_Site constructor.
	 */
	public function __construct() {

		$this->settings();
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_post_type_support( 'page', 'excerpt' );  // Added excerpt option to pages.

		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'cmb2_admin_init', array( $this, 'register_header_metabox' ) );
		add_action( 'pre_get_posts', array( $this, 'tags_support_query' ) );
		add_action( 'admin_init', array( $this, 'add_copyright_text' ) );

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_parent_styles' ) );
		parent::__construct();
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

  	/*
	* Define settings for the Planet4 Master Theme.
	*/
	protected function settings() {
		Timber::$autoescape = true;
		Timber::$dirname = array( 'templates', 'views' );
		$this->theme_dir = get_template_directory_uri();
		$this->theme_images_dir = $this->theme_dir . '/images/';
	}

	/**
	 * Load styling and behaviour.
	 */
	public function enqueue_parent_styles() {
		wp_enqueue_style( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css', array(), '4.0.0-alpha.6' );
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/style.css' );
		wp_register_script( 'jquery', 'https://code.jquery.com/jquery-3.2.1.min.js', array(), null, true );
		wp_enqueue_script( 'main', $this->theme_dir . '/assets/js/main.js', array( 'jquery' ), null, true );
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
			'home_url'     => esc_url( home_url( '/' ) ),
			'search_query' => get_search_query(),
		];
		$context['domain'] = 'planet4-master-theme';
		$context['foo']    = 'bar';   // For unit test purposes.
		$context['site']   = $this;
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

new P4_Master_Site();
