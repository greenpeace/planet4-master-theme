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
		add_action( 'init',                   array( $this, 'register_p4_post_type_taxonomy' ) );
		add_action( 'pre_get_posts',          array( $this, 'add_search_options' ) );
		add_filter( 'searchwp_query_orderby', array( $this, 'edit_searchwp_query_orderby' ), 10, 2 );
		add_action( 'cmb2_admin_init',        array( $this, 'register_header_metabox' ) );
		add_action( 'pre_get_posts',          array( $this, 'tags_support_query' ) );
		add_action( 'admin_init',             array( $this, 'add_copyright_text' ) );
		add_action( 'admin_init',             array( $this, 'add_google_tag_manager_identifier_setting' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts',     array( $this, 'enqueue_public_assets' ) );
		add_action( 'save_post', 			  array($this, 'p4_save_post_type'), 10, 2 );

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
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/style.css' );
		wp_register_script( 'jquery-3', 'https://code.jquery.com/jquery-3.2.1.min.js', array(), '3.2.1', true );
		wp_enqueue_script( 'bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js', array(), '4.0.0-beta', true );
		wp_enqueue_script( 'main', $this->theme_dir . '/assets/js/main.js', array( 'jquery' ), null, true );
	}

	/**
	 * Register a custom taxonomy for planet4 post types
	 */
	public function register_p4_post_type_taxonomy() {

		$p4_post_type = [
			'name'              => _x( 'Planet4 Post Types', 'taxonomy general name' ),
			'singular_name'     => _x( 'Planet4 Post Type', 'taxonomy singular name' ),
			'search_items'      => __( 'Search in Planet4 Post Type' ),
			'all_items'         => __( 'All Planet4 Post Types' ),
			'most_used_items'   => null,
			'parent_item'       => null,
			'parent_item_colon' => null,
			'edit_item'         => __( 'Edit Planet4 Post Type' ),
			'update_item'       => __( 'Update Planet4 Post Type' ),
			'add_new_item'      => __( 'Add new Planet4 Post Type' ),
			'new_item_name'     => __( 'New Planet4 Post Type' ),
			'menu_name'         => __( 'Planet4 Post Types' ),
		];
		$args         = [
			'hierarchical' => false,
			'labels'       => $p4_post_type,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => [
				'slug' => 'p4-post-types',
			],
			'meta_box_cb' => [$this, 'p4_metabox_markup']
		];
		register_taxonomy( 'p4-post-type', [ 'p4_post_type', 'post' ], $args );

		$terms = [
			'0' => [
				'name'        => 'Story',
				'slug'        => 'story',
				'description' => 'A term for story post type',
			],
			'1' => [
				'name'        => 'Press release',
				'slug'        => 'press-release',
				'description' => 'A term for press release post type',
			],
			'2' => [
				'name'        => 'Publication',
				'slug'        => 'publication',
				'description' => 'A term for publication post type',
			],
		];

		foreach ( $terms as $term_key => $term ) {
			$res = wp_insert_term(
				$term['name'],
				'p4-post-type',
				[
					'description' => $term['description'],
					'slug'        => $term['slug'],
				]
			);
			unset( $term );
		}
	}

	/**
	 * Save custom taxonomy for planet4 post types
	 */
	public function p4_save_post_type( $post_id ) {
		// some of these checks might be redundant, but they're all nicely
		// separated and easy to delete so I'll leave them for now.

		// ignore autosave
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// check nonce
		if ( ! isset( $_POST['p4-post-type-nonce'] ) || ! wp_verify_nonce( $_POST['p4-post-type-nonce'], basename( __FILE__ ) ) ) {
			return;
		}
		// check user's capabilities
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// make sure there's input
		if ( ! isset( $_POST['p4-post-type'] ) ) {
			return;
		}
		// make sure the term exists
		if( ! $selected = get_term_by('slug', sanitize_text_field( $_POST['p4-post-type']), 'p4-post-type') ) {
			return;
		}
		// make sure it's not an error
		if ( is_wp_error( $selected ) ) {
			return $post_id;
		}

		// save post type
		$result = wp_set_post_terms( $post_id, $selected->slug, 'p4-post-type', $append = false );
		return;
	}


	/**
	 * Add a dropdown to choose planet4 post type.
	 */
	public function p4_metabox_markup( $object ) {
		get_post_meta( $object->ID );
		$current = -1;
		if( $current_term = get_the_terms($object, 'p4-post-type') ) {

			if(! is_wp_error( $current_term )) {
				$current = $current_term[0]->slug;
			}
		}

		$terms = get_terms('p4-post-type', [ 'hide_empty' => false ]);

		wp_nonce_field( basename( __FILE__ ), 'p4-post-type-nonce' );
		?><div>
			<select name="p4-post-type"><?
				foreach($terms as $k => $term) {
					$selected = ( $current === $term->slug ) ? 'selected="selected"' : '';
				?>
					<option <?= $selected ?> value="<?= $term->slug ?>"><?= $term->name ?></option>
				<? }
			$selected = ( -1 === $current ) ? 'selected="selected"' : '';
			?><option value="-1" <?= $selected ?> >none</option>
			</select>
		</div><?

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

		$p4_post = new_cmb2_box( array(
			'id'           => $prefix . 'metabox_post',
			'title'        => __( 'Post Articles Element Fields', 'planet4-master-theme' ),
			'object_types' => array( 'post' ),
		) );

		$p4_post->add_field( array(
			'name' => __( 'Articles Title', 'planet4-master-theme' ),
			'desc' => __( 'Title for articles block', 'planet4-master-theme' ),
			'id'   => $prefix . 'articles_title',
			'type' => 'text_medium',
		) );

		$p4_post->add_field( array(
				'name'       => __( 'Articles Count', 'planet4-master-theme' ),
				'desc'       => __( 'Number of articles that should be displayed for articles block', 'planet4-master-theme' ),
				'id'         => $prefix . 'articles_count',
				'type'       => 'text_medium',
				'attributes' => array(
					'type' => 'number',
				),
			)
		);

		$p4_post->add_field( array(
			'name' => __( 'Author Override', 'planet4-master-theme' ),
			'desc' => __( 'Enter author name if you want to override the author', 'planet4-master-theme' ),
			'id'   => $prefix . 'author_override',
			'type' => 'text_medium',
		) );
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
