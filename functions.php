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

		add_filter( 'timber_context',           array( $this, 'add_to_context' ) );
		add_filter( 'get_twig',                 array( $this, 'add_to_twig' ) );
		add_action( 'init',                     array( $this, 'register_taxonomies' ), 2 );
		add_action( 'init',                     array( $this, 'register_oembed_provider' ) );
		add_action( 'pre_get_posts',            array( $this, 'add_search_options' ) );
		add_filter( 'searchwp_query_main_join', array( $this, 'edit_searchwp_main_join_action_pages' ), 10, 2 );
		add_filter( 'searchwp_query_orderby',   array( $this, 'edit_searchwp_orderby_action_pages' ) );
		add_filter( 'searchwp_query_orderby',   array( $this, 'edit_searchwp_query_orderby' ), 11, 2 );
		add_filter( 'posts_where',              array( $this, 'edit_search_mime_types' ) );
		add_action( 'cmb2_admin_init',          array( $this, 'register_header_metabox' ) );
		add_action( 'admin_enqueue_scripts',    array( $this, 'enqueue_admin_assets' ) );
		add_action( 'wp_enqueue_scripts',       array( $this, 'enqueue_public_assets' ) );
		add_filter( 'wp_kses_allowed_html',     array( $this, 'set_custom_allowed_attributes_filter' ) );
		add_action( 'add_meta_boxes',           array( $this, 'add_meta_box_search' ) );
		add_action( 'save_post',                array( $this, 'save_meta_box_search' ), 10, 2 );
		add_action( 'save_post',                array( $this, 'set_featured_image' ), 10, 3 );
		add_action( 'after_setup_theme',        array( $this, 'p4_master_theme_setup' ) );
		add_action( 'admin_menu',               array( $this, 'add_restricted_tags_box' ) );
		add_action( 'do_meta_boxes',            array( $this, 'remove_default_tags_box' ) );
		add_action( 'pre_insert_term',          array( $this, 'disallow_insert_term' ), 1, 2 );
		add_filter( 'wp_image_editors',         array( $this, 'allowedEditors' ) );
		add_filter( 'jpeg_quality',             function( $arg ) { return 60; } );
		add_action( 'after_setup_theme',        array( $this, 'add_image_sizes' ) );
		add_action( 'admin_head' ,              array( $this, 'remove_add_post_element' ) );
		add_filter( 'post_gallery',             array( $this, 'carousel_post_gallery' ), 10, 2 );
		add_action( 'save_post',                array( $this, 'p4_auto_generate_excerpt' ) , 10, 2 );
		add_filter( 'img_caption_shortcode',    array( $this, 'override_img_caption_shortcode' ), 10, 3 );

		add_action( 'wp_ajax_get_paged_posts',        array( 'P4_Search', 'get_paged_posts' ) );
		add_action( 'wp_ajax_nopriv_get_paged_posts', array( 'P4_Search', 'get_paged_posts' ) );

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		register_nav_menus( array(
			'navigation-bar-menu' => __( 'Navigation Bar Menu', 'planet4-master-theme-backend' ),
		) );
	}

	/**
	 * Sets as featured image of the post the first image found attached in the post's content (if any).
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post The current Post.
	 * @param bool    $update Whether this is an existing post being updated or not.
	 */
	public function set_featured_image( $post_id, $post, $update ) {
		global $wpdb;

		// Ignore autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check user's capabilities.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		// Check if user has set the featured image manually or if he has removed it.
		$user_set_featured_image = get_post_meta( $post_id, '_thumbnail_id', true );

		// Apply this behavior to Posts only.
		if ( 'post' === $post->post_type && ! $user_set_featured_image ) {

			// Find all matches of <img> html tags within the post's content and get the url inside the src attribute.
			preg_match_all( '/<img.+src=[\'"]([^\'"]+)[\'"].*>/i', $post->post_content, $matches );
			if ( isset( $matches[1][0] ) ) {
				$first_img_url = $matches[1][0];

				// Use the attachment's url to find its id.
				$statement = $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE guid='%s';", $first_img_url );
				$result    = $wpdb->get_col( $statement );

				if ( isset( $result[0] ) ) {
					$attachment_id = $result[0];
					set_post_thumbnail( $post_id, $attachment_id );
				}
			}
		}
	}

	/**
	 * Add extra image sizes as needed.
	 */
	public function add_image_sizes() {
		add_image_size( 'retina-large', 2048, 1366, false );
		add_image_size( 'articles-medium-large', 510, 340, false );
	}

	/**
	 * Force WordPress to use P4_Image_Compression as image manipulation editor.
	 */
	public function allowedEditors() {
		return [ 'P4_Image_Compression' ];
	}

	/**
	 * Load translations for master theme
	 */
	function p4_master_theme_setup() {
		$domains = [
			'planet4-master-theme',
			'planet4-master-theme-backend',
		];
		$locale  = is_admin() ? get_user_locale() : get_locale();

		foreach ( $domains as $domain ) {
			$mofile = get_template_directory() . '/languages/' . $domain . '-' . $locale . '.mo';
			load_textdomain( $domain, $mofile );
		}
	}

	/**
	 * Inject dependencies.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function services( $services = array() ) {
		if ( $services ) {
			foreach ( $services as $service ) {
				$this->services[ $service ] = new $service();
			}
		}
	}

	/**
	 * Gets the loaded services.
	 *
	 * @return array The loaded services.
	 */
	public function get_services() : array {
		return $this->services;
	}

	/**
	 * Adds more data to the context variable that will be passed to the main template.
	 *
	 * @param array $context The associative array with data to be passed to the main template.
	 *
	 * @return mixed
	 */
	public function add_to_context( $context ) {
		global $wp;
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
		if ( function_exists( 'icl_get_languages' ) ) {
			$context['languages']  = count( icl_get_languages() );
		}
		$context['navbar_menu']  = new TimberMenu( 'navigation-bar-menu' );
		$context['site']         = $this;
		$context['current_url']  = home_url( $wp->request );
		$context['sort_options'] = $this->sort_options;
		$context['default_sort'] = P4_Search::DEFAULT_SORT;


		$options                         = get_option( 'planet4_options' );

		// Do not embed google tag manager js if 'greenpeace' cookie is not set or enforce_cookies_policy setting is not enabled.
		$enforce_cookies_policy = isset( $options['enforce_cookies_policy'] ) ? true : false;
		if ( $enforce_cookies_policy ) {
			$cookie_consent              = isset( $_COOKIE['greenpeace'] ) ? $_COOKIE['greenpeace'] : false;
			$gtm                         = $options['google_tag_manager_identifier'] ?? '';
			$context['google_tag_value'] = ! empty( $gtm ) && '2' === $cookie_consent ? $gtm : '';
		} else {
			$context['google_tag_value'] = $options['google_tag_manager_identifier'] ?? '';
		}

		$context['donatelink']           = $options['donate_button'] ?? '#';
		$context['website_navbar_title'] = $options['website_navigation_title'] ?? __( 'International (English)', 'planet4-master-theme' );

		// Footer context.
		$context['copyright_text_line1']  = $options['copyright_line1'] ?? '';
		$context['copyright_text_line2']  = $options['copyright_line2'] ?? '';
		$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
		$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
		$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
		$context['p4_comments_depth']     = get_option( 'thread_comments_depth' ) ?? 1; // Default depth level set to 1 if not selected from admin.
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
			'align'           => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'name'            => true,
			'src'             => true,
			'id'              => true,
			'class'           => true,
			'style'           => true,
			'scrolling'       => true,
			'marginwidth'     => true,
			'marginheight'    => true,
			'allowfullscreen' => true,
		];

		// Allow blockquote and the following attributes. (trigger: allow instagram embeds)
		$allowedposttags['blockquote'] = [
			'style'                  => true,
			'data-instgrm-captioned' => true,
			'data-instgrm-permalink' => true,
			'data-instgrm-version'   => true,
			'class'                  => true,
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

		$allowedposttags['script'] = [
			'src' => true,
		];

		// Allow below tags for carousel slider
		$allowedposttags['div']['data-ride']    = true;
		$allowedposttags['li']['data-target']   = true;
		$allowedposttags['li']['data-slide-to'] = true;
		$allowedposttags['a']['data-slide']     = true;
		$allowedposttags['span']['aria-hidden'] = true;

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
	public function enqueue_admin_assets( $hook ) {
		// Register jQuery 3 for use wherever needed by adding wp_enqueue_script( 'jquery-3' );.
		wp_register_script( 'jquery-3', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), '3.3.1', true );
	}

	/**
	 * Load styling and behaviour on website pages.
	 */
	public function enqueue_public_assets() {
		$css_creation = filectime( get_template_directory() . '/style.css' );
		$js_creation  = filectime( get_template_directory() . '/assets/js/main.js' );

		// CSS files
		wp_enqueue_style( 'bootstrap', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/css/bootstrap.min.css', array(), '4.1.1' );
		wp_enqueue_style( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.css', array(), '1.9.0' );
		wp_enqueue_style( 'font-awesome', $this->theme_dir . '/assets/lib/@fortawesome/fontawesome-free-webfonts/css/fontawesome.css', array(), '5.0.10' );
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/style.css', [], $css_creation );
		// JS files
		wp_register_script( 'jquery', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', array(), '3.3.1', true );
		wp_enqueue_script( 'popperjs', 'https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.3/umd/popper.min.js', array(), '1.14.3', true );
		wp_enqueue_script( 'bootstrapjs', 'https://cdnjs.cloudflare.com/ajax/libs/twitter-bootstrap/4.1.1/js/bootstrap.min.js', array(), '4.1.1', true );
		wp_enqueue_script( 'main', $this->theme_dir . '/assets/js/main.js', array( 'jquery' ), $js_creation, true );
		wp_enqueue_script( 'slick', 'https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.9.0/slick.min.js', array(), '1.9.0', true );
		wp_enqueue_script( 'hammer', 'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js', array(), '2.0.8', true );
	}

	/**
	 * Creates a Metabox on the side of the Add/Edit Post/Page
	 * that is used for applying weight to the current Post/Page in search results.
	 *
	 * @param WP_Post $post The currently Added/Edited post.
	 */
	public function add_meta_box_search( $post ) {
		add_meta_box( 'meta-box-search','Search', array( $this, 'view_meta_box_search' ), [ 'post', 'page' ], 'side', 'default', $post );
	}

	/**
	 * Renders a Metabox on the side of the Add/Edit Post/Page.
	 *
	 * @param WP_Post $post The currently Added/Edited post.
	 */
	public function view_meta_box_search( $post ) {
		$weight  = get_post_meta( $post->ID, 'weight', true );
		$options = get_option( 'planet4_options' );

		echo '<label for="my_meta_box_text">' . esc_html__( 'Weight (1-30)', 'planet4-master-theme-backend' ) . '</label>
				<input id="weight" type="text" name="weight" value="' . esc_attr( $weight ) . '" />';
		?><script>
			$ = jQuery;
			$( '#parent_id' ).off('change').on( 'change', function () {
				// Check selected Parent page and give bigger weight if it will be an Action page
				if ( '<?php echo esc_js( $options['act_page'] ); ?>' === $(this).val() ) {
					$( '#weight' ).val( <?php echo esc_js( P4_Search::DEFAULT_ACTION_WEIGHT ); ?> );
				} else {
					$( '#weight' ).val( <?php echo esc_js( P4_Search::DEFAULT_PAGE_WEIGHT ); ?> );
				}
			});
		</script>
		<?php
	}

	/**
	 * Saves the Search weight of the Post/Page.
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post The current Post.
	 */
	public function save_meta_box_search( $post_id, $post ) {
		global $pagenow;

		// Ignore autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}
		// Check user's capabilities.
		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}
		// Make sure there's input.
		$weight = filter_input( INPUT_POST, 'weight', FILTER_VALIDATE_INT, [
			'options' => [
				'min_range' => P4_Search::DEFAULT_MIN_WEIGHT,
				'max_range' => P4_Search::DEFAULT_MAX_WEIGHT,
			],
		] );

		// If this is a new Page then set default weight for it.
		if ( ! $weight && 'post-new.php' === $pagenow ) {
			if ( 'page' === $post->post_type ) {
				$weight = P4_Search::DEFAULT_PAGE_WEIGHT;
			}
		}

		// Store weight.
		update_post_meta( $post_id, 'weight', $weight );
	}

	/**
	 * Registers taxonomies.
	 */
	public function register_taxonomies() {
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
	 * Edit the searchwp main join clause, so that it can boost Action Pages priority
	 * based on a custom meta_key that holds the weight.
	 *
	 * @param string $sql The main JOIN clause.
	 * @param string $engine The SearchWP selected engine.
	 *
	 * @return string The edited JOIN statement.
	 */
	public function edit_searchwp_main_join_action_pages( $sql, $engine ) : string {
		global $wpdb;

		$meta_key = 'weight';  // The meta_key you want to order by.
		$sql .= " LEFT JOIN {$wpdb->postmeta} ON {$wpdb->posts}.ID = {$wpdb->postmeta}.post_id AND {$wpdb->postmeta}.meta_key = '{$meta_key}' AND {$wpdb->postmeta}.meta_value != ''";
		return $sql;
	}

	/**
	 * Customize the order of search results when sorting by Most Relevant, so that it boosts Action pages up.
	 *
	 * @param string $orderby The ORDER BY sql clause.
	 *
	 * @return string The edited ORDER BY clause.
	 */
	public function edit_searchwp_orderby_action_pages( $orderby ) : string {
		global $wpdb;

		$orderby     = str_replace( 'ORDER BY', '', $orderby );
		$new_orderby = "ORDER BY {$wpdb->postmeta}.meta_value+0 DESC, " . $orderby;
		return $new_orderby;
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
			// First orderby 'weight' meta_key.
			$primary_sort   = 'meta_value';
			$primary_order  = 'DESC';
			// If 'weight' is same then orderby selected_order.
			$selected_order = $this->sort_options[ $selected_sort ]['order'];
			$orderby        = esc_sql( sprintf( 'ORDER BY %s %s, %s %s', $primary_sort, $primary_order, $selected_sort, $selected_order ) );
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
		// TODO - This method and all Search related methods in this class
		// TODO - after this commit CAN and SHOULD be transferred inside the P4_Search class.
		// TODO - Would have spotted the necessary change much faster.
		$search_action = filter_input( INPUT_GET, 'search-action', FILTER_SANITIZE_STRING );

		if ( is_search() || wp_doing_ajax() && ( 'get_paged_posts' === $search_action ) ) {
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

		if ( 0 !== absint( $parent_act_id ) ) {
			$take_action_pages_args = [
				'post_type'        => 'page',
				'post_parent'      => $parent_act_id,
				'post_status'      => 'publish',
				'orderby'          => 'menu_order',
				'order'            => 'ASC',
				'suppress_filters' => false,
				'numberposts'      => -1,
			];

			$posts = get_posts( $take_action_pages_args );
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
			'title'        => __( 'Page Header Fields', 'planet4-master-theme-backend' ),
			'object_types' => array( 'page' ), // Post type.
		) );

		$p4_header->add_field( array(
			'name' => __( 'Header Title', 'planet4-master-theme-backend' ),
			'desc' => __( 'Header title comes here', 'planet4-master-theme-backend' ),
			'id'   => $prefix . 'title',
			'type' => 'text_medium',
		) );

		$p4_header->add_field(
			array(
				'name' => __( 'Header Subtitle', 'planet4-master-theme-backend' ),
				'desc' => __( 'Header subtitle comes here', 'planet4-master-theme-backend' ),
				'id'   => $prefix . 'subtitle',
				'type' => 'text_medium',
			)
		);

		$p4_header->add_field(
			array(
				'name'    => __( 'Header Description', 'planet4-master-theme-backend' ),
				'desc'    => __( 'Header description comes here', 'planet4-master-theme-backend' ),
				'id'      => $prefix . 'description',
				'type'    => 'wysiwyg',
				'options' => array(
					'textarea_rows' => 5,
					'media_buttons' => false,
				),
			)
		);

		$p4_header->add_field(
			array(
				'name' => __( 'Header Button Title', 'planet4-master-theme-backend' ),
				'desc' => __( 'Header button title comes here', 'planet4-master-theme-backend' ),
				'id'   => $prefix . 'button_title',
				'type' => 'text_medium',
			)
		);

		$p4_header->add_field(
			array(
				'name' => __( 'Header Button Link', 'planet4-master-theme-backend' ),
				'desc' => __( 'Header button link comes here', 'planet4-master-theme-backend' ),
				'id'   => $prefix . 'button_link',
				'type' => 'text_medium',
			)
		);

		$p4_header->add_field(
			array(
				'name'         => __( 'Background overide', 'planet4-master-theme-backend' ),
				'desc'         => __( 'Upload an image', 'planet4-master-theme-backend' ),
				'id'           => 'background_image',
				'type'         => 'file',
				// Optional
				'options'      => array(
					'url' => false,
				),
				'text'         => array(
					'add_upload_file_text' => __( 'Add Background Image', 'planet4-master-theme-backend' )
				),
				'query_args'   => array(
					'type' => 'image',
				),
				'preview_size' => 'large',
			)
		);

		$p4_post = new_cmb2_box( [
			'id'           => $prefix . 'metabox_post',
			'title'        => __( 'Post Articles Element Fields', 'planet4-master-theme-backend' ),
			'object_types' => [ 'post' ],
		] );

		$p4_post->add_field( [
			'name' => __( 'Author Override', 'planet4-master-theme-backend' ),
			'desc' => __( 'Enter author name if you want to override the author', 'planet4-master-theme-backend' ),
			'id'   => $prefix . 'author_override',
			'type' => 'text_medium',
		] );

		$p4_post->add_field( [
			'name'             => __( 'Take Action Page Selector', 'planet4-master-theme-backend' ),
			'desc'             => __( 'Select a Take Action Page to populate take action boxout block', 'planet4-master-theme-backend' ),
			'id'               => $prefix . 'take_action_page',
			'type'             => 'select',
			'show_option_none' => true,
			'options_cb'       => [ $this, 'populate_act_page_children_options' ],
		] );

		$p4_post->add_field( [
			'name'       => __( 'Include Articles In Post', 'planet4-master-theme-backend' ),
			'id'         => 'include_articles',
			'type'       => 'select',
			'options'    => [
				'yes' => 'Yes',
				'no'  => 'No',
			],
		] );

		$p4_post->add_field( [
			'name'         => __( 'Background Image Override', 'planet4-master-theme-backend' ),
			'desc'         => __( 'Upload an image or select one from the media library to override the background image', 'planet4-master-theme-backend' ),
			'id'           => $prefix . 'background_image_override',
			'type'         => 'file',
			'options'      => [
				'url' => false,
			],
			'text'         => [
				'add_upload_file_text' => __( 'Add Image', 'planet4-master-theme-backend' ),
			],
			'preview_size' => 'large',
		] );
	}

	/**
	 * Add restricted tags box for all roles besides administrator.
	 * A list of checkboxes representing the tags will be rendered.
	 */
	public function add_restricted_tags_box() {

		if ( current_user_can( 'administrator' ) ) {
			return;
		}
		add_meta_box( 'restricted_tags_box',
			__( 'Tags' ),
			[ $this, 'print_restricted_tags_box' ],
			[ 'post', 'page' ],
			'side'
		);
	}

	/**
	 * Remove "Add Post Element" button for POST & rename on page as "Add Page Element".
	 */
	function remove_add_post_element() {
		if ( 'post' === get_post_type() ) {
			remove_action( 'media_buttons', [ Shortcode_UI::get_instance(), 'action_media_buttons' ] );
		}

		if ( 'page' === get_post_type() ) {
			remove_action( 'media_buttons', [ Shortcode_UI::get_instance(), 'action_media_buttons' ] );
			add_action( 'media_buttons', [ $this, 'action_page_media_buttons' ] );
		}
	}

	/**
	 * Output an "Add Page Element" button with the media buttons.
	 */
	public function action_page_media_buttons( $editor_id ) {
		printf( '<button type="button" class="button shortcake-add-post-element" data-editor="%s">' .
		        '<span class="wp-media-buttons-icon dashicons dashicons-migrate"></span> %s' .
		        '</button>',
			esc_attr( $editor_id ),
			__( 'Add Page Element', 'planet4-master-theme-backend' )
		);
	}

	/**
	 * Apply carousel style to wp image gallery.
	 */
	public function carousel_post_gallery( $output, $attr) {
		return do_shortcode('[shortcake_carousel multiple_image="' . $attr['ids'] . '"]');
	}

	/**
	 * Auto generate excerpt for post.
	 *
	 * @param int $post_id Id of the saved post.
	 * @param WP_Post $post Post object.
	 */
	public function p4_auto_generate_excerpt( $post_id, $post ) {
		if ( '' === $post->post_excerpt && 'post' === $post->post_type ) {

			// Unhook save_post function so it doesn't loop infinitely.
			remove_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10 );

			// Update the post, which calls save_post again.
			wp_update_post( [
				'ID'           => $post_id,
				'post_excerpt' => strip_tags( apply_filters( 'the_content', wp_trim_words( strip_tags( $post->post_content ), 30 ) ) ),
			] );

			// re-hook save_post function.
			add_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10, 2 );
		}
	}

	/**
	 * Restrict creation of tags from all roles besides administrator.
	 *
	 * @param string $term The term to be added.
	 * @param string $taxonomy Taxonomy slug.
	 *
	 * @return WP_Error|string
	 */
	public function disallow_insert_term( $term, $taxonomy ) {

		$user = wp_get_current_user();

		if ( 'post_tag' === $taxonomy && ! in_array( 'administrator', (array) $user->roles, true ) ) {

			return new WP_Error(
				'disallow_insert_term',
				__( 'Your role does not have permission to add terms to this taxonomy' )
			);

		}

		return $term;
	}

	/**
	 * Fetch all tags and find which are assinged to the post and pass them as arguments to tags box template.
	 *
	 * @param WP_Post $post Post object.
	 */
	public function print_restricted_tags_box( $post ) {
		$all_post_tags = get_terms( 'post_tag', [ 'get' => 'all' ] );
		$assigned_tags = wp_get_object_terms( $post->ID, 'post_tag' );

		$assigned_ids = [];
		foreach ( $assigned_tags as $assigned_tag ) {
			$assigned_ids[] = $assigned_tag->term_id;
		}

		$this->render_partial( 'partials/tags_box', [ 'tags' => $all_post_tags, 'assigned_tags' => $assigned_ids ] );
	}

	/**
	 * Remove default WordPress tags selection box for all roles besides administrator.
	 */
	public function remove_default_tags_box() {

		if ( current_user_can( 'administrator' ) ) {
			return;
		}

		remove_meta_box( 'tagsdiv-post_tag', [ 'post', 'page' ], 'normal' );
		remove_meta_box( 'tagsdiv-post_tag', [ 'post', 'page' ], 'side' );
	}

	/**
	 * Load a partial template and pass variables to it.
	 *
	 * @param string $path  path to template file, minus .php (eg. `content-page`, `partial/template-name`).
	 * @param array  $args  array of variables to load into scope.
	 */
	private function render_partial( $path, $args = [] ) {
		if ( ! empty( $args ) ) {
			extract( $args );
		}
		include( locate_template( $path . '.php' ) );
	}

	/**
	 * Filter function for img_caption_shortcode. Append image credit to caption.
	 *
	 * @param string $output  The caption output. Passed empty by WordPress.
	 * @param array  $attr    Attributes of the caption shortcode.
	 * @param string $content The image element, possibly wrapped in a hyperlink.
	 *
	 * @return string HTML content to display the caption.
	 */
	public function override_img_caption_shortcode( $output, $attr, $content ) {

		$atts = shortcode_atts( array(
			'id'      => '',
			'align'   => 'alignnone',
			'width'   => '',
			'caption' => '',
			'class'   => '',
		), $attr, 'caption' );

		$image_id     = trim( str_replace( 'attachment_', '', $atts['id'] ) );
		$meta         = get_post_meta( $image_id );
		$image_credit = '';
		if ( isset( $meta['_credit_text'] ) && ! empty( $meta['_credit_text'][0] ) ) {
			$image_credit = ' ' . $meta['_credit_text'][0];
			if ( ! is_numeric( strpos( $meta['_credit_text'][0], '©' ) ) ) {
				$image_credit = ' ©' . $image_credit;
			}
		}

		$class        = trim( 'wp-caption ' . $atts['align'] . ' ' . $atts['class'] );

		if ( $atts['id'] ) {
			$atts['id'] = 'id="' . esc_attr( $atts['id'] ) . '" ';
		}

		$output = '<div ' . $atts['id'] . ' class="' . esc_attr( $class ) . '">'
				. do_shortcode( $content ) . '<p class="wp-caption-text">' . $atts['caption'] . $image_credit . '</p></div>';

		return $output;
	}
}

new P4_Master_Site( [
	'P4_Custom_Taxonomy',
	'P4_Taxonomy_Image',
	'P4_Settings',
	'P4_Control_Panel',
	'P4_Post_Report_Controller',
	'P4_Cookies',
] );
