<?php


if ( ! class_exists( 'Timber' ) ) {
	add_action( 'admin_notices', function() {
		echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . esc_url( admin_url( 'plugins.php#timber' ) ) . '">' . esc_url( admin_url( 'plugins.php') ) . '</a></p></div>';
	});

	add_filter('template_include', function($template) {
		return get_stylesheet_directory() . '/static/no-timber.html';
	});

	return;
}

Timber::$dirname = array('templates', 'views');

class P4_Master_Site extends TimberSite {

	protected $child_css = array();

	public function __construct() {
		
		add_theme_support( 'post-formats' );
		add_theme_support( 'post-thumbnails' );
		add_theme_support( 'menus' );
		add_post_type_support( 'page', 'excerpt' );  // Added excerpt option to pages

		add_filter( 'timber_context', array( $this, 'add_to_context' ) );
		add_filter( 'get_twig', array( $this, 'add_to_twig' ) );

		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'cmb2_admin_init', array( $this, 'register_header_metabox' ) );
		add_action( 'pre_get_posts', array( $this, 'tags_support_query' ) );

		// Default actions are in: https://core.trac.wordpress.org/browser/tags/4.5.3/src/wp-includes/default-filters.php#L0
		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_parent_styles') );
		parent::__construct();
	}


	public function enqueue_parent_styles() {
		wp_enqueue_style( 'bootstrap', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css', array(), '4.0.0-alpha.6' );
		wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
		wp_register_script('jquery', 'https://code.jquery.com/jquery-3.2.1.min.js', array(), null, true);
		wp_enqueue_script( 'main', get_template_directory_uri() . '/assets/js/main.js', array('jquery'), null, true );
	}

	/**
	 *
	 */
	public function register_post_types() {
		//this is where you can register custom post types

	}

	/**
	 * To register taxonomies for page.
	 */
	public function register_taxonomies() { 
		register_taxonomy_for_object_type( 'post_tag', 'page' );
		register_taxonomy_for_object_type( 'category', 'page' );
	}

	/**
	 * Query on tags and categories.
	 *
	 * @param  wp_query.
	 */
	public function tags_support_query( $wp_query ) {
		if ( $wp_query->get('tag') ) {
			$wp_query->set( 'post_type', 'any' );
		}

		if ( $wp_query->get('category_name') ) {
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
			'title'         => esc_html__( 'Page Header Fields', 'planet4-master-theme' ),
			'object_types'  => array( 'page' ), // Post type
		) );

		$p4_header->add_field( array(
			'name' => esc_html__( 'Header Title', 'planet4-master-theme' ),
			'desc' => esc_html__( 'Header title comes here', 'planet4-master-theme' ),
			'id'   => $prefix . 'title',
			'type' => 'text_medium',
		) );

		$p4_header->add_field( array(
			'name' => esc_html__( 'Header Subtitle', 'planet4-master-theme' ),
			'desc' => esc_html__( 'Header subtitle comes here', 'planet4-master-theme' ),
			'id'   => $prefix . 'subtitle',
			'type' => 'text_medium',
		) );

		$p4_header->add_field( array(
			'name'    => esc_html__( 'Header Description', 'planet4-master-theme' ),
			'desc'    => esc_html__( 'Header description comes here', 'planet4-master-theme' ),
			'id'      => $prefix . 'description',
			'type'    => 'wysiwyg',
			'options' => array(
				'textarea_rows' => 5,
			),
		) );

		$p4_header->add_field( array(
			'name' => esc_html__( 'Header Button Title', 'planet4-master-theme' ),
			'desc' => esc_html__( 'Header button title comes here', 'planet4-master-theme' ),
			'id'   => $prefix . 'button_title',
			'type' => 'text_medium',
		) );

		$p4_header->add_field( array(
			'name' => esc_html__( 'Header Button Link', 'planet4-master-theme' ),
			'desc' => esc_html__( 'Header button link comes here', 'planet4-master-theme' ),
			'id'   => $prefix . 'button_link',
			'type' => 'text_medium',
		) );
	}

	public function add_to_context( $context ) {
		$context['foo']  = 'bar';               // For unit test purposes.
		$context['menu'] = new TimberMenu();
		$context['site'] = $this;
		return $context;
	}

	public function add_to_twig( $twig ) {
		/* this is where you can add your own functions to twig */
		$twig->addExtension( new Twig_Extension_StringLoader() );
		return $twig;
	}
}

new P4_Master_Site();