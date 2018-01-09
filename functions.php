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

use Timber\Timber;
use Timber\Site as TimberSite;
use Timber\Menu as TimberMenu;

/**
 * Class P4_Master_Site.
 * The main class that handles Planet4 Master Theme.
 */
class P4_Master_Site extends TimberSite {

	/** @var string $theme_dir */
	protected $theme_dir;
	/** @var string $theme_images_dir */
	protected $theme_images_dir;
	/** @var array $sort_options */
	protected $sort_options;
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
				if ( strpos( $class_name, 'P4_' ) !== false ) {
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
		$this->sort_options     = [
			'relevant'  => [
				'name'  => __( 'Most relevant', 'planet4-master-theme' ),
				'order' => 'DESC',
			],
			'post_date' => [
				'name'  => __( 'Most recent', 'planet4-master-theme' ),
				'order' => 'DESC',
			],
			//'post_title' => [
			//	'name'  => __( 'Title', 'planet4-master-theme' ),
			//	'order' => 'ASC',
			//],
		];
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
		add_action( 'init',                   array( $this, 'register_taxonomies' ) );
		add_action( 'init',                   array( $this, 'register_oembed_provider' ) );
		add_action( 'pre_get_posts',          array( $this, 'add_search_options' ) );
		add_filter( 'searchwp_query_orderby', array( $this, 'edit_searchwp_query_orderby' ), 10, 2 );
		add_filter( 'posts_where',            array( $this, 'edit_search_mime_types' ) );
		add_action( 'cmb2_admin_init',        array( $this, 'register_header_metabox' ) );
		add_action( 'pre_get_posts',          array( $this, 'tags_support_query' ) );
		add_action( 'admin_enqueue_scripts',  array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts',     array( $this, 'enqueue_public_assets' ) );
		add_filter( 'wp_kses_allowed_html',   array( $this, 'set_custom_allowed_attributes_filter' ) );
		add_action( 'save_post',              array( $this, 'p4_save_page_type' ) );

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		register_nav_menus( array(
			'navigation-bar-menu' => __( 'Navigation Bar Menu', 'planet4-master-theme' ),
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
	 * Adds more data to the context variable that will be passed to the main template.
	 *
	 * @param array $context The associative array with data to be passed to the main template.
	 *
	 * @return mixed
	 */
	public function add_to_context( $context ) {
		$context['cookies'] = [
			'text' => planet4_get_option( 'cookies_field' ),
		];
		$context['data_nav_bar'] = [
			'images'       => $this->theme_images_dir,
			'home_url'     => home_url( '/' ),
			'search_query' => trim( get_search_query() ),
		];
		$context['domain']       = 'planet4-master-theme';
		$context['foo']          = 'bar';   // For unit test purposes.
		$context['navbar_menu']  = new TimberMenu( 'navigation-bar-menu' );
		$context['site']         = $this;
		$context['sort_options'] = $this->sort_options;
		$context['default_sort']  = P4_Search::DEFAULT_SORT;

		$options                          = get_option( 'planet4_options' );
		$context['donatelink']            = $options['donate_button'] ?? '#';
		$context['google_tag_value']      = $options['google_tag_manager_identifier'] ?? '';

		// Footer context.
		$context['copyright_text']        = $options['copyright'] ?? '';
		$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
		$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
		$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );

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

	/**
	 * Set attributes that should be allowed for posts filter
	 * Allow img srcset and sizes attributes.
	 * Allow iframes in posts.
	 *
	 * @param array $allowedposttags Default allowed tags.
	 *
	 * @return array
	 */
	public function set_custom_allowed_attributes_filter( $allowedposttags ) {
		// Allow iframes and the following attributes.
		$allowedposttags['iframe'] = [
			'align'        => true,
			'width'        => true,
			'height'       => true,
			'frameborder'  => true,
			'name'         => true,
			'src'          => true,
			'id'           => true,
			'class'        => true,
			'style'        => true,
			'scrolling'    => true,
			'marginwidth'  => true,
			'marginheight' => true,
		];

		// Allow img and the following attributes.
		$allowedposttags['img'] = [
			'alt'    => true,
			'class'  => true,
			'id'     => true,
			'height' => true,
			'hspace' => true,
			'name'   => true,
			'src'    => true,
			'srcset' => true,
			'sizes'  => true,
			'width'  => true,
			'vspace' => true,
		];

		return $allowedposttags;
	}

	/**
	 * Sanitizes the settings input.
	 *
	 * @param string $setting The setting to sanitize.
	 *
	 * @return string The sanitized setting.
	 */
	public function sanitize( $setting ) : string {
		$allowed = [
			'ul'     => [],
			'ol'     => [],
			'li'     => [],
			'strong' => [],
			'del'    => [],
			'span'  => [
				'style' => [],
			],
			'p' => [
				'style' => [],
			],
			'a' => [
				'href'   => [],
				'target' => [],
				'rel'    => [],
			],
		];
		return wp_kses( $setting, $allowed );
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
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/style.css', [], '0.0.28'  );
		wp_enqueue_style( 'slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.css', array(), '1.8.1' );
		wp_register_script( 'jquery-3', 'https://code.jquery.com/jquery-3.2.1.min.js', array(), '3.2.1', true );
		wp_enqueue_script( 'popperjs', $this->theme_dir . '/assets/js/popper.min.js', array(), '1.11.0', true );
		wp_enqueue_script( 'bootstrapjs', 'https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-beta/js/bootstrap.min.js', array(), '4.0.0-beta', true );
		wp_enqueue_script( 'main', $this->theme_dir . '/assets/js/main.js', array( 'jquery' ), '0.2.1', true );
		wp_enqueue_script( 'custom', $this->theme_dir . '/assets/js/custom.js', array( 'jquery' ), '0.1.4', true );
		wp_enqueue_script( 'slick', 'https://cdn.jsdelivr.net/npm/slick-carousel@1.8.1/slick/slick.min.js', array(), '0.1.0', true );
		if ( is_search() ) {
			wp_enqueue_script( 'search', $this->theme_dir . '/assets/js/search.js', array( 'jquery' ), '0.1.2', true );
		}
	}

	/**
	 * Register a custom taxonomy for planet4 page types
     	 */
	public function register_p4_page_type_taxonomy() {

		$p4_page_type = [
			'name'              => _x( 'Page Types', 'taxonomy general name' ),
			'singular_name'     => _x( 'Page Type', 'taxonomy singular name' ),
			'search_items'      => __( 'Search in Page Type' ),
			'all_items'         => __( 'All Page Types' ),
			'most_used_items'   => null,
			'parent_item'       => null,
			'parent_item_colon' => null,
			'edit_item'         => __( 'Edit Page Type' ),
			'update_item'       => __( 'Update Page Type' ),
			'add_new_item'      => __( 'Add new Page Type' ),
			'new_item_name'     => __( 'New Page Type' ),
			'menu_name'         => __( 'Page Types' ),
		];
		$args         = [
			'hierarchical' => false,
			'labels'       => $p4_page_type,
			'show_ui'      => true,
			'query_var'    => true,
			'rewrite'      => [
				'slug' => 'p4-page-types',
			],
			'meta_box_cb'  => [ $this, 'p4_metabox_markup' ]
		];
		register_taxonomy( 'p4-page-type', [ 'p4_page_type', 'post' ], $args );

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
			wp_insert_term(
				$term['name'],
				'p4-page-type',
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
	 *
	 * @param int $post_id Id of the saved post.
	 */
	public function p4_save_page_type( $post_id ) {
		// Ignore autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check nonce.
		if ( ! isset( $_POST['p4-page-type-nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['p4-page-type-nonce'] ) ), 'p4-save-page-type' ) ) {
			return;
		}
		// Check user's capabilities.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// Make sure there's input.
		if ( ! isset( $_POST['p4-page-type'] ) ) { // Input var okay.
			return;
		}
		// If "none" was selected, remove the term.
		if ( $_POST['p4-page-type'] === '-1' ) {
			wp_set_post_terms( $post_id, [], 'p4-page-type' );

			return;
		}
		// Make sure the term exists and it's not an error.
		$selected = get_term_by( 'slug', sanitize_text_field( wp_unslash( $_POST['p4-page-type'] ) ), 'p4-page-type' ); // Input var okay.
		if ( false === $selected || is_wp_error( $selected ) ) {
			return;
		}
		// Save post type.
		wp_set_post_terms( $post_id, sanitize_text_field( $selected->slug ), 'p4-page-type' );
	}

	/**
	 * Add a dropdown to choose planet4 post type.
	 *
	 * @param WP_Post $post
	 */
	public function p4_metabox_markup( WP_Post $post ) {
		$attached_type = get_the_terms( $post, 'p4-page-type' );
		$current_type  = ( is_array( $attached_type ) ) ? $attached_type[0]->slug : -1;
		$all_types     = get_terms( 'p4-page-type', [ 'hide_empty' => false ] );
		wp_nonce_field( 'p4-save-page-type', 'p4-page-type-nonce' );
		?>
		<select name="p4-page-type">
			<?php foreach ( $all_types as $term ) : ?>
				<option <?php selected( $current_type, $term->slug ); ?> value="<?php echo esc_attr( $term->slug ); ?>">
					<?php echo esc_html( $term->name ); ?>
				</option>
			<?php endforeach; ?>
			<option value="-1" <?php selected( -1, $current_type ); ?> >none</option>
		</select>
		<?php
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
		// Call function for p4 post type custom taxonomy.
		$this->register_p4_page_type_taxonomy();
		register_taxonomy_for_object_type( 'post_tag', 'page' );
		register_taxonomy_for_object_type( 'category', 'page' );
	}

	/**
	 * Registers oembed provider for Carto map.
	 */
	public function register_oembed_provider() {
		wp_oembed_add_provider( '#https?://(?:www\.)?[^/^\.]+\.carto(db)?\.com/\S+#i', 'https://services.carto.com/oembed', true );
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

		$wp->set( 'posts_per_page', P4_Search::POSTS_LIMIT );
		$wp->set( 'no_found_rows', true );
	}

	/**
	 * Customize the order of search results.
	 *
	 * @param string $orderby The ORDER BY sql clause.
	 *
	 * @return string The customized part of the query related to the ORDER BY.
	 */
	public function edit_searchwp_query_orderby( $orderby ) : string {
		$selected_sort = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
		$selected_sort = sanitize_sql_orderby( $selected_sort );

		if ( $selected_sort && P4_Search::DEFAULT_SORT !== $selected_sort ) {
			$selected_order = $this->sort_options[ $selected_sort ]['order'];
			$orderby        = esc_sql( sprintf( 'ORDER BY %s %s', $selected_sort, $selected_order ) );
		} else {
			$orderby = esc_sql( $orderby );
		}

		return $orderby;
	}

	/**
	 * Customize which mime types we want to search for regarding attachments.
	 *
	 * @param string $where The WHERE clause of the query.
	 *
	 * @return string The edited WHERE clause.
	 */
	public function edit_search_mime_types( $where ) : string {
		if ( is_search() ) {
			$mime_types = implode( ',', P4_Search::DOCUMENT_TYPES );
			$where .= ' AND post_mime_type IN("' . $mime_types . '","") ';
		}
		return $where;
	}

	/**
	 * Populate an associative array with all the children of the ACT page
	 *
	 * @return array
	 */
	public function populate_act_page_children_options() {
		$parent_act_id = planet4_get_option( 'act_page' );
		$options       = [];

		if( 0 !== absint( $parent_act_id ) ) {
			$take_action_pages_args = [
				'post_type'   => 'page',
				'post_parent' => $parent_act_id,
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
			'id'           => $prefix . 'metabox',
			'title'        => __( 'Page Header Fields', 'planet4-master-theme' ),
			'object_types' => array( 'page' ), // Post type.
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
				'name'         => __( 'Background overide', 'planet4-master-theme' ),
				'desc'         => __( 'Upload an image', 'planet4-master-theme' ),
				'id'           => 'background_image',
				'type'         => 'file',
				// Optional
				'options'      => array(
					'url' => false,
				),
				'text'         => array(
					'add_upload_file_text' => __( 'Add Background Image', 'planet4-master-theme' )
				),
				'query_args'   => array(
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
			'name'    => __( 'Articles Title', 'planet4-master-theme' ),
			'desc'    => __( 'Title for articles block', 'planet4-master-theme' ),
			'id'      => $prefix . 'articles_title',
			'type'    => 'text_medium',
			'default' => planet4_get_option( 'articles_block_title', '' ) ?? '',
		] );

		$p4_post->add_field( [
			'name'       => __( 'Articles Count', 'planet4-master-theme' ),
			'desc'       => __( 'Number of articles that should be displayed for articles block', 'planet4-master-theme' ),
			'id'         => $prefix . 'articles_count',
			'type'       => 'text_medium',
			'default'    => planet4_get_option( 'articles_count', '' ) ?? '',
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
}

new P4_Master_Site( [
	'P4_Taxonomy_Image',
	'P4_Settings',
	'P4_Control_Panel',
] );
