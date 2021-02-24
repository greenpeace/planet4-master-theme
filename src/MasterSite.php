<?php

namespace P4\MasterTheme;

use DateTimeImmutable;
use Timber\Timber;
use Timber\Site as TimberSite;
use Timber\Menu as TimberMenu;
use Twig_Extension_StringLoader;
use Twig_ExtensionInterface;
use Twig_SimpleFilter;
use WP_Error;
use WP_Post;
use WP_User;

/**
 * Class MasterSite.
 * The main class that handles Planet4 Master Theme.
 */
class MasterSite extends TimberSite {

	/**
	 * Key of notice seen by user
	 *
	 * @var string
	 */
	private const DASHBOARD_MESSAGE_KEY = 'last_p4_notice';

	/**
	 * Version of notice
	 *
	 * @var string
	 */
	private const DASHBOARD_MESSAGE_VERSION = '0.2';

	/**
	 * Theme directory
	 *
	 * @var string $theme_dir
	 */
	protected $theme_dir;

	/**
	 * Theme images directory
	 *
	 * @var string $theme_images_dir
	 */
	protected $theme_images_dir;

	/**
	 * Sort options
	 *
	 * @var array $sort_options
	 */
	protected $sort_options;

	/**
	 * Variable that lets us know if the user has or hasn't used google to log in
	 *
	 * @var boolean $google_login_error
	 */
	protected $google_login_error = false;

	/**
	 * MasterSite constructor.
	 */
	public function __construct() {
		$this->settings();
		$this->hooks();
		parent::__construct();
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
			'_score'        => [
				'name'  => __( 'Most relevant', 'planet4-master-theme' ),
				'order' => 'DESC',
			],
			'post_date'     => [
				'name'  => __( 'Newest', 'planet4-master-theme' ),
				'order' => 'DESC',
			],
			'post_date_asc' => [
				'name'  => __( 'Oldest', 'planet4-master-theme' ),
				'order' => 'ASC',
			],
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

		add_filter( 'timber_context', [ $this, 'add_to_context' ] );
		add_filter( 'get_twig', [ $this, 'add_to_twig' ] );
		add_action( 'init', [ $this, 'register_taxonomies' ], 2 );
		add_action( 'init', [ $this, 'register_oembed_provider' ] );
		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
		add_filter( 'safe_style_css', [ $this, 'set_custom_allowed_css_properties' ] );
		add_filter( 'wp_kses_allowed_html', [ $this, 'set_custom_allowed_attributes_filter' ], 10, 2 );
		add_action( 'add_meta_boxes', [ $this, 'add_meta_box_search' ] );
		add_action( 'save_post', [ $this, 'save_meta_box_search' ], 10, 2 );
		add_action( 'save_post', [ $this, 'set_featured_image' ], 10, 3 );
		add_action( 'post_updated', [ $this, 'clean_post_cache' ], 10, 3 );
		add_action( 'after_setup_theme', [ $this, 'p4_master_theme_setup' ] );
		add_action( 'admin_menu', [ $this, 'add_restricted_tags_box' ] );
		add_action( 'do_meta_boxes', [ $this, 'remove_default_tags_box' ] );
		add_action( 'pre_insert_term', [ $this, 'disallow_insert_term' ], 1, 2 );
		add_filter( 'wp_dropdown_users_args', [ $this, 'filter_authors' ], 10, 1 );
		add_filter( 'wp_image_editors', [ $this, 'allowedEditors' ] );
		add_filter(
			'jpeg_quality',
			function( $arg ) {
				return 60;
			}
		);
		add_filter(
			'http_request_timeout',
			function( $timeout ) {
				return 10;
			}
		);
		add_action( 'after_setup_theme', [ $this, 'add_image_sizes' ] );
		add_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10, 2 );

		if ( wp_doing_ajax() ) {
			Search::add_general_filters();
		}
		add_action( 'wp_ajax_get_paged_posts', [ ElasticSearch::class, 'get_paged_posts' ] );
		add_action( 'wp_ajax_nopriv_get_paged_posts', [ ElasticSearch::class, 'get_paged_posts' ] );

		add_action( 'admin_head', [ $this, 'add_help_sidebar' ] );

		remove_action( 'wp_head', 'print_emoji_detection_script', 7 );
		remove_action( 'wp_head', 'wp_generator' );
		remove_action( 'wp_print_styles', 'print_emoji_styles' );

		register_nav_menus(
			[
				'navigation-bar-menu' => __( 'Navigation Bar Menu', 'planet4-master-theme-backend' ),
			]
		);

		add_filter( 'authenticate', [ $this, 'enforce_google_signon' ], 4, 3 );
		add_filter( 'authenticate', [ $this, 'check_google_login_error' ], 30, 3 );
		add_filter( 'login_headerurl', [ $this, 'add_login_logo_url' ] );
		add_filter( 'login_headertext', [ $this, 'add_login_logo_url_title' ] );
		add_action( 'login_enqueue_scripts', [ $this, 'add_login_stylesheet' ] );
		add_filter( 'comment_form_submit_field', [ $this, 'gdpr_cc_comment_form_add_class' ], 150, 2 );
		add_filter( 'embed_oembed_html', [ $this, 'filter_youtube_oembed_nocookie' ], 10, 2 );
		add_filter(
			'editable_roles',
			function( $roles ) {
				uasort(
					$roles,
					function( $a, $b ) {
						return $b['name'] <=> $a['name'];
					}
				);
				return $roles;
			}
		);

		/**
		 * Apply wpautop to non-block content.
		 *
		 * @link https://wordpress.stackexchange.com/q/321662/26317
		 *
		 * @param string $block_content The HTML generated for the block.
		 * @param array  $block         The block.
		 */
		add_filter(
			'render_block',
			function ( $block_content, $block ) {
				if ( is_null( $block['blockName'] ) ) {
					return wpautop( $block_content );
				}
				return $block_content;
			},
			10,
			2
		);

		add_action( 'init', [ $this, 'login_redirect' ], 1 );
		add_filter( 'attachment_fields_to_edit', [ $this, 'add_image_attachment_fields_to_edit' ], 10, 2 );
		add_filter( 'attachment_fields_to_save', [ $this, 'add_image_attachment_fields_to_save' ], 10, 2 );
		version_compare( get_bloginfo( 'version' ), '5.5', '<' )
			? add_action( 'init', [ $this, 'p4_register_core_image_block' ] )
			: add_filter( 'register_block_type_args', [ $this, 'register_core_blocks_callback' ] );
		add_action( 'admin_notices', [ $this, 'show_dashboard_notice' ] );
		add_action( 'wp_ajax_dismiss_dashboard_notice', [ $this, 'dismiss_dashboard_notice' ] );

		// Pin the ElasticPress to v3.4 search algorithm.
		simple_value_filter( 'ep_search_algorithm_version', '3.4' );

		// Update P4 author override value in RSS feed.
		add_filter(
			'the_author',
			function ( $post_author ) {
				if ( is_feed() ) {
					global $post;
					$author_override = get_post_meta( $post->ID, 'p4_author_override', true );
					if ( '' !== $author_override ) {
						$post_author = $author_override;
					}
				}
				return $post_author;
			}
		);

		// Disable xmlrpc.
		add_filter( 'xmlrpc_enabled', '__return_false' );
	}

	/**
	 * Detects and redirects login from non-canonical domain to preferred domain
	 */
	public function login_redirect() {
		if ( ! isset( $GLOBALS['pagenow'] ) || 'wp-login.php' !== $GLOBALS['pagenow'] ) {
			// Not on the login page, as you were.
			return;
		}

		if ( ! isset( $_SERVER['HTTP_HOST'] ) || ! isset( $_SERVER['SERVER_NAME'] ) ) {
			// If either of these are unset, we can't be sure we want to redirect.
			return;
		}

		if ( $_SERVER['HTTP_HOST'] !== $_SERVER['SERVER_NAME'] ) {
			if ( wp_safe_redirect( str_replace( sanitize_text_field( wp_unslash( $_SERVER['HTTP_HOST'] ) ), sanitize_text_field( wp_unslash( $_SERVER['SERVER_NAME'] ) ), get_admin_url() ) ) ) {
				exit;
			}
		}
	}

	/**
	 * Sets the URL for the logo link in the login page.
	 */
	public function add_login_logo_url() {
		return home_url();
	}

	/**
	 * Sets the title for the logo link in the login page.
	 */
	public function add_login_logo_url_title() {
		return get_bloginfo( 'name' );
	}

	/**
	 * Sets a custom stylesheet for the login page.
	 */
	public function add_login_stylesheet() {
		wp_enqueue_style(
			'custom-login',
			$this->theme_dir . '/admin/css/login.css',
			[],
			Loader::theme_file_ver( 'admin/css/login.css' )
		);
	}

	/**
	 * Sets as featured image of the post the first image found attached in the post's content (if any).
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post The current Post.
	 * @param bool    $update Whether this is an existing post being updated or not.
	 */
	public function set_featured_image( $post_id, $post, $update ) {

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

		// Apply this behavior to a Post and only if it does not already a featured image.
		if ( 'post' === $post->post_type && ! $user_set_featured_image ) {
			// Find all matches of <img> html tags within the post's content and get the id of the image from the elements class name.
			preg_match_all( '/<img.+wp-image-(\d+).*>/i', $post->post_content, $matches );
			if ( isset( $matches[1][0] ) && is_numeric( $matches[1][0] ) ) {
				set_post_thumbnail( $post_id, $matches[1][0] );
			}
		}
	}

	/**
	 * Generates hreflang metadata from countries.json template.
	 */
	public static function generate_hreflang_meta(): ?array {

		$countries = wp_cache_get( 'countries' );

		if ( false === $countries ) {
			$body      = file_get_contents( get_template_directory() . '/templates/countries.json' );
			$countries = json_decode( $body, true );
			if ( empty( $countries ) ) {
				return null;
			}
			wp_cache_set( 'countries', $countries );
		}

		$metadata = [];

		foreach ( $countries as $key => $letter ) {
			if ( 0 === $key ) {
				continue;
			}
			foreach ( $letter as $country ) {
				$lang = $country['lang'];
				foreach ( $lang as $item ) {
					if ( isset( $item['locale'] ) ) {
						foreach ( $item['locale'] as $code ) {
							$metadata[ $code ] = $item['url'];
						}
					}
				}
			}
		}

		return $metadata;
	}

	/**
	 * Sets as featured image of the post the first image found attached in the post's content (if any).
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post_after The current Post.
	 * @param WP_Post $post_before Whether this is an existing post being updated or not.
	 */
	public function clean_post_cache( $post_id, $post_after, $post_before ) {

		// Ignore autosave.
		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		$this->clean_boxout_posts_cache( $post_id, $post_after, $post_before );

		clean_post_cache( $post_id );
	}

	/**
	 * Flush Take Action Boxout(TAB) posts cache, if the TAB act page status changes.
	 *
	 * @param int     $post_id The ID of the current Post.
	 * @param WP_Post $post_after The current Post.
	 * @param WP_Post $post_before Whether this is an existing post being updated or not.
	 */
	private function clean_boxout_posts_cache( $post_id, $post_after, $post_before ): void {
		$parent_act_id = (int) planet4_get_option( 'act_page' );
		if ( 'page' !== $post_after->post_type || $parent_act_id !== $post_after->post_parent ) {
			return;
		}

		// Flush cache only when a page status changes from publish to any non-public status & vice versa.
		if (
			( $post_before->post_status === $post_after->post_status ) ||
			( 'publish' !== $post_before->post_status && 'publish' !== $post_after->post_status )
		) {
			return;
		}

		global $wpdb, $nginx_purger;

		// Search for those posts, who use TAB($post_id) from "Take Action Page Selector" dropdown.
		// phpcs:disable
		$sql          = 'SELECT post_id FROM %1$s WHERE meta_key = \'p4_take_action_page\' AND meta_value = %2$d';
		$prepared_sql = $wpdb->prepare( $sql, $wpdb->postmeta, $post_id );
		$boxout_posts = $wpdb->get_col( $prepared_sql );
		// phpcs:enable

		// Search for those posts, who use TAB($post_id) as a block inside block editor.
		$take_action_boxout_block = '%<!-- wp:planet4-blocks/take-action-boxout {"take_action_page":' . $post_id . '} /-->%';
		// phpcs:disable
		$sql          = 'SELECT ID FROM %1$s WHERE post_type = \'post\' AND post_status = \'publish\' AND post_content LIKE \'%2$s\'';
		$prepared_sql = $wpdb->prepare( $sql, $wpdb->posts, $take_action_boxout_block );
		$result       = $wpdb->get_col( $prepared_sql );
		// phpcs:enable

		$boxout_posts = array_merge( $boxout_posts, $result );

		// Flush TAB posts cache.
		$boxout_posts = array_unique( $boxout_posts );
		foreach ( $boxout_posts as $tab_post_id ) {
			clean_post_cache( $tab_post_id );
			$tab_post_url = get_permalink( $tab_post_id );
			$nginx_purger->purge_url( user_trailingslashit( $tab_post_url ) );
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
	 * Force WordPress to use ImageCompression as image manipulation editor.
	 */
	public function allowedEditors() {
		return [ ImageCompression::class ];
	}

	/**
	 * Load translations for master theme
	 */
	public function p4_master_theme_setup() {
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
	 * Adds more data to the context variable that will be passed to the main template.
	 *
	 * @param array $context The associative array with data to be passed to the main template.
	 *
	 * @return mixed
	 */
	public function add_to_context( $context ) {
		global $wp;
		$context['cookies']      = [
			'text' => planet4_get_option( 'cookies_field' ),
		];
		$context['data_nav_bar'] = [
			'images'                  => $this->theme_images_dir,
			'home_url'                => home_url( '/' ),
			'search_query'            => trim( get_search_query() ),
			'country_dropdown_toggle' => __( 'Toggle worldwide site selection menu', 'planet4-master-theme' ),
			'navbar_search_toggle'    => __( 'Toggle search box', 'planet4-master-theme' ),
		];
		$context['domain']       = 'planet4-master-theme';
		$context['foo']          = 'bar';   // For unit test purposes.
		if ( function_exists( 'icl_get_languages' ) ) {
			$context['languages'] = count( icl_get_languages() );
		}
		$context['navbar_menu']  = new TimberMenu( 'navigation-bar-menu' );
		$context['site']         = $this;
		$context['current_url']  = home_url( $wp->request );
		$context['sort_options'] = $this->sort_options;
		$context['default_sort'] = Search::DEFAULT_SORT;

		$options = get_option( 'planet4_options' );

		// Do not embed google tag manager js if 'greenpeace' cookie is not set or enforce_cookies_policy setting is not enabled.
		$context['enforce_cookies_policy'] = isset( $options['enforce_cookies_policy'] ) ? true : false;
		$context['google_tag_value']       = $options['google_tag_manager_identifier'] ?? '';
		$context['ab_hide_selector']       = $options['ab_hide_selector'] ?? null;
		$context['facebook_page_id']       = $options['facebook_page_id'] ?? '';
		$context['preconnect_domains']     = [];

		if ( ! empty( $options['preconnect_domains'] ) ) {
			$preconnect_domains = explode( "\n", $options['preconnect_domains'] );
			$preconnect_domains = array_map( 'trim', $preconnect_domains );
			$preconnect_domains = array_filter( $preconnect_domains );

			$context['preconnect_domains'] = $preconnect_domains;
		}

		// hreflang metadata.
		if ( is_front_page() ) {
			$context['hreflang'] = self::generate_hreflang_meta();
		}

		// Datalayer feed.
		$current_user = wp_get_current_user();
		if ( $current_user->ID ) {
			$context['p4_signedin_status'] = 'true';
			$context['p4_visitor_type']    = $current_user->roles[0] ?? '';
		} else {
			$context['p4_signedin_status'] = 'false';
			$context['p4_visitor_type']    = 'guest';
		}

		$context['donatelink']           = $options['donate_button'] ?? '#';
		$context['donatetext']           = $options['donate_text'] ?? __( 'Donate', 'planet4-master-theme' );
		$context['website_navbar_title'] = $options['website_navigation_title'] ?? __( 'International (English)', 'planet4-master-theme' );
		if ( is_front_page() && isset( $options['donate_btn_visible_on_mobile'] ) && $options['donate_btn_visible_on_mobile'] ) {
			$context['enhanced_donate_btn_class'] = 'btn-enhanced-donate';
		} else {
			$context['enhanced_donate_btn_class'] = '';
		}

		$context['act_page_id']     = $options['act_page'] ?? '';
		$context['explore_page_id'] = $options['explore_page'] ?? '';

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
		$twig->addFilter( new Twig_SimpleFilter( 'svgicon', [ $this, 'svgicon' ] ) );

		return $twig;
	}

	/**
	 * SVG Icon helper
	 *
	 * @param string $name Icon name.
	 */
	public function svgicon( $name ) {
		$svg_icon_template = '<svg viewBox="0 0 32 32" class="icon"><use xlink:href="' . $this->theme_dir . '/images/symbol/svg/sprite.symbol.svg#' . $name . '"></use></svg>';
		return new \Twig_Markup( $svg_icon_template, 'UTF-8' );
	}

	/**
	 * Set CSS properties that should be allowed for posts filter
	 * Allow img object-position.
	 *
	 * @param array $allowedproperties Default allowed CSS properties.
	 *
	 * @return array
	 */
	public function set_custom_allowed_css_properties( $allowedproperties ) {
		$allowedproperties[] = 'object-position';
		$allowedproperties[] = '--spreadsheet-header-background';
		$allowedproperties[] = '--spreadsheet-even-row-background';
		$allowedproperties[] = '--spreadsheet-odd-row-background';

		return $allowedproperties;
	}

	/**
	 * Set HTML attributes that should be allowed for posts filter
	 * Allow img srcset and sizes attributes.
	 * Allow iframes in posts.
	 *
	 * @see wp_kses_allowed_html()
	 *
	 * @param array  $allowedposttags Default allowed tags.
	 * @param string $context         The context for which to retrieve tags.
	 *
	 * @return array
	 */
	public function set_custom_allowed_attributes_filter( $allowedposttags, $context ) {
		if ( 'post' !== $context ) {
			return $allowedposttags;
		}

		// Allow iframes and the following attributes.
		$allowedposttags['style']  = [];
		$allowedposttags['iframe'] = [
			'align'           => true,
			'width'           => true,
			'height'          => true,
			'frameborder'     => true,
			'name'            => true,
			'src'             => true,
			'srcdoc'          => true,
			'id'              => true,
			'class'           => true,
			'style'           => true,
			'scrolling'       => true,
			'marginwidth'     => true,
			'marginheight'    => true,
			'allowfullscreen' => true,
		];

		// Allow blockquote and the following attributes. (trigger: allow instagram embeds).
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
			'style'  => true,
			'vspace' => true,
		];

		$allowedposttags['script'] = [
			'src'    => true,
			'id'     => true,
			'data-*' => true,
		];

		// Allow source tag for WordPress audio shortcode to function.
		$allowedposttags['source'] = [
			'type' => true,
			'src'  => true,
		];

		// Allow below tags for carousel slider.
		$allowedposttags['div']['data-ride']    = true;
		$allowedposttags['li']['data-target']   = true;
		$allowedposttags['li']['data-slide-to'] = true;
		$allowedposttags['a']['data-slide']     = true;
		$allowedposttags['span']['aria-hidden'] = true;

		// Allow below tags for spreadsheet block.
		$allowedposttags['input'] = [
			'class'       => true,
			'type'        => true,
			'placeholder' => true,
		];

		$allowedposttags['lite-youtube'] = [
			'videoid' => true,
			'params'  => true,
			'style'   => true,
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
			'span'   => [
				'style' => [],
			],
			'p'      => [
				'style' => [],
			],
			'a'      => [
				'href'   => [],
				'target' => [],
				'rel'    => [],
			],
		];
		return wp_kses( $setting, $allowed );
	}

	/**
	 * Load styling and behaviour on admin pages.
	 *
	 * @param string $hook Hook.
	 */
	public function enqueue_admin_assets( $hook ) {
		// Register jQuery 3 for use wherever needed by adding wp_enqueue_script( 'jquery-3' );.
		wp_register_script( 'jquery-3', 'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js', [], '3.3.1', true );
	}

	/**
	 * Load styling and behaviour on website pages.
	 */
	public function enqueue_public_assets() {
		// master-theme assets.
		$css_creation = filectime( get_template_directory() . '/assets/build/style.min.css' );
		$js_creation  = filectime( get_template_directory() . '/assets/build/index.js' );

		// CSS files.
		wp_enqueue_style(
			'bootstrap',
			$this->theme_dir . '/assets/build/bootstrap.min.css',
			[],
			Loader::theme_file_ver( 'assets/build/bootstrap.min.css' )
		);

		// This loads a linked style file since the relative images paths are outside the build directory.
		wp_enqueue_style( 'parent-style', $this->theme_dir . '/assets/build/style.min.css', [], $css_creation );

		$jquery_deps = is_plugin_active( 'planet4-plugin-gutenberg-blocks/planet4-gutenberg-blocks.php' ) ? [ 'planet4-blocks-frontend' ] : [];

		// JS files.
		wp_deregister_script( 'jquery' );
		wp_register_script(
			'jquery',
			'https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js',
			$jquery_deps,
			'3.3.1',
			true
		);

		// Variables reflected from PHP to the JS side.
		$localized_variables = [
			// The ajaxurl variable is a global js variable defined by WP itself but only for the WP admin
			// For the frontend we need to define it ourselves and pass it to js.
			'ajaxurl'           => admin_url( 'admin-ajax.php' ),
			'show_scroll_times' => Search::SHOW_SCROLL_TIMES,
		];

		wp_register_script( 'main', $this->theme_dir . '/assets/build/index.js', [ 'jquery' ], $js_creation, true );
		wp_localize_script( 'main', 'localizations', $localized_variables );
		wp_enqueue_script( 'main' );
		wp_enqueue_script( 'youtube', $this->theme_dir . '/assets/build/lite-yt-embed.js', [], 1, true );
	}

	/**
	 * Creates a Metabox on the side of the Add/Edit Post/Page
	 * that is used for applying weight to the current Post/Page in search results.
	 *
	 * @param WP_Post $post The currently Added/Edited post.
	 */
	public function add_meta_box_search( $post ) {
		add_meta_box( 'meta-box-search', 'Search', [ $this, 'view_meta_box_search' ], [ 'post', 'page' ], 'side', 'default', $post );
	}

	/**
	 * Renders a Metabox on the side of the Add/Edit Post/Page.
	 *
	 * @param WP_Post $post The currently Added/Edited post.
	 */
	public function view_meta_box_search( $post ) {
		$weight  = get_post_meta( $post->ID, 'weight', true );
		$options = get_option( 'planet4_options' );

		echo '<label for="my_meta_box_text">' . esc_html__( 'Weight', 'planet4-master-theme-backend' ) . ' (1-' . esc_attr( Search::DEFAULT_MAX_WEIGHT ) . ')</label>
				<input id="weight" type="text" name="weight" value="' . esc_attr( $weight ) . '" />';
		?><script>
			$ = jQuery;
			$( '#parent_id' ).off('change').on( 'change', function () {
				// Check selected Parent page and give bigger weight if it will be an Action page
				if ( '<?php echo esc_js( $options['act_page'] ); ?>' === $(this).val() ) {
					$( '#weight' ).val( <?php echo esc_js( Search::DEFAULT_ACTION_WEIGHT ); ?> );
				} else {
					$( '#weight' ).val( <?php echo esc_js( Search::DEFAULT_PAGE_WEIGHT ); ?> );
				}
			});
		</script>
		<?php
	}

	/**
	 * Applies filters for list of users in dropdown
	 *
	 * @param null|Array $args The filter options and values.
	 */
	public function filter_authors( $args ) {
		if ( isset( $args['who'] ) ) {
			$args['role__in'] = [ 'administrator', 'author', 'campaigner', 'contributor', 'editor' ];
			unset( $args['who'] );
		}
		return $args;
	}

	/**
	 * Forces a user to login using Google Auth if they have a greenpeace.org email
	 *
	 * @param null|WP_User|WP_Error $user The current user logging in.
	 * @param null|String           $username The username of the user.
	 * @param null|String           $password The password of the user.
	 * @return null|WP_User|WP_Error
	 */
	public function enforce_google_signon( $user, $username = null, $password = null ) {

		if ( defined( 'WP_DEBUG' ) && WP_DEBUG === true ) {
			return $user;
		}

		if ( empty( $username ) || empty( $password ) ) {
			return $user;
		} elseif ( strpos( $username, '@' ) ) {
			$user_data = get_user_by( 'email', trim( wp_unslash( $username ) ) );
		} else {
			$login     = trim( $username );
			$user_data = get_user_by( 'login', $login );
		}

		if ( empty( $user_data ) || is_wp_error( $user ) ) {
			return $user;
		}

		$email_user_name = mb_substr( $user_data->data->user_email, 0, strpos( $user_data->data->user_email, '@' ) );

		// Dont enforce google login on aliases.
		if ( strpos( $email_user_name, '+' ) ) {
			return $user;
		}

		$domain = '@greenpeace.org';
		if ( mb_substr( $user_data->data->user_email, -strlen( $domain ) ) === $domain ) {
			$this->google_login_error = true;
		}

		return $user;
	}

	/**
	 * Checks if we have set a google login error earlier on so we can prevent login if google login wasn't used
	 *
	 * @param null|WP_User|WP_Error $user The current user logging in.
	 * @param null|String           $username The username of the user.
	 * @param null|String           $password The password of the user.
	 * @return null|WP_User|WP_Error
	 */
	public function check_google_login_error( $user, $username = null, $password = null ) {
		if ( $this->google_login_error ) {
			$this->google_login_error = false;
			return new WP_Error( 'google_login', __( 'You are trying to login with a Greenpeace email. Please use the Google login button instead.', 'planet4-master-theme-backend' ) );
		}

		return $user;
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
		$weight = filter_input(
			INPUT_POST,
			'weight',
			FILTER_VALIDATE_INT,
			[
				'options' => [
					'min_range' => Search::DEFAULT_MIN_WEIGHT,
					'max_range' => Search::DEFAULT_MAX_WEIGHT,
				],
			]
		);

		// If this is a new Page then set default weight for it.
		if ( ! $weight && 'post-new.php' === $pagenow ) {
			if ( 'page' === $post->post_type ) {
				$weight = Search::DEFAULT_PAGE_WEIGHT;
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
	 * Add restricted tags box for all roles besides administrator.
	 * A list of checkboxes representing the tags will be rendered.
	 */
	public function add_restricted_tags_box() {

		if ( current_user_can( 'administrator' ) ) {
			return;
		}
		add_meta_box(
			'restricted_tags_box',
			__( 'Tags', 'planet4-master-theme-backend' ),
			[ $this, 'print_restricted_tags_box' ],
			[ 'post', 'page' ],
			'side'
		);
	}

	/**
	 * Auto generate excerpt for post.
	 *
	 * @param int     $post_id Id of the saved post.
	 * @param WP_Post $post Post object.
	 */
	public function p4_auto_generate_excerpt( $post_id, $post ) {
		if ( '' === $post->post_excerpt && 'post' === $post->post_type ) {

			// Unhook save_post function so it doesn't loop infinitely.
			remove_action( 'save_post', [ $this, 'p4_auto_generate_excerpt' ], 10 );

			// Generate excerpt text.
			$post_excerpt = strip_shortcodes( $post->post_content );

			preg_match( '/<p>(.*?)<\/p>/', $post_excerpt, $match_paragraph );

			$post_excerpt   = $match_paragraph[1] ?? $post_excerpt;
			$post_excerpt   = apply_filters( 'the_content', $post_excerpt );
			$post_excerpt   = str_replace( ']]>', ']]&gt;', $post_excerpt );
			$excerpt_length = apply_filters( 'excerpt_length', 30 );
			$excerpt_more   = apply_filters( 'excerpt_more', '&hellip;' );
			$post_excerpt   = wp_trim_words( $post_excerpt, $excerpt_length, $excerpt_more );

			// Update the post, which calls save_post again.
			wp_update_post(
				[
					'ID'           => $post_id,
					'post_excerpt' => $post_excerpt,
				]
			);

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
				__( 'Your role does not have permission to add terms to this taxonomy', 'planet4-master-theme-backend' )
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

		$this->render_partial(
			'partials/tags-box',
			[
				'tags'          => $all_post_tags,
				'assigned_tags' => $assigned_ids,
			]
		);
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
			extract( $args, EXTR_OVERWRITE ); // phpcs:ignore WordPress.PHP.DontExtract.extract_extract
		}
		include locate_template( $path . '.php' );
	}

	/**
	 * Add a help link to the Help sidebars.
	 */
	public function add_help_sidebar() {
		if ( get_current_screen() ) {
			$screen  = get_current_screen();
			$sidebar = $screen->get_help_sidebar();

			$sidebar .= '<p><a target="_blank" href="https://planet4.greenpeace.org/">Planet 4 Handbook</a></p>';

			$screen->set_help_sidebar( $sidebar );
		}
	}

	/**
	 * Filter and add class to GDPR consent checkbox label after the GDPR fields appended to comment form submit field.
	 *
	 * @param string $submit_field The HTML content of comment form submit field.
	 * @param array  $args         The arguments array.
	 *
	 * @return string HTML content of comment form submit field.
	 */
	public function gdpr_cc_comment_form_add_class( $submit_field, $args ) {

		$pattern[0]     = '/(for=["\']gdpr-comments-checkbox["\'])/';
		$replacement[0] = '$1 class="custom-control-description"';
		$pattern[1]     = '/(id=["\']gdpr-comments-checkbox["\'])/';
		$replacement[1] = '$1 style="width:auto;"';
		$pattern[2]     = '/id="gdpr-comments-compliance"/';
		$replacement[2] = 'id="gdpr-comments-compliance" class="custom-control"';

		$submit_field = preg_replace( $pattern, $replacement, $submit_field );

		return $submit_field;
	}

	/**
	 * Filter function for embed_oembed_html.
	 * Transform youtube embeds to youtube-nocookie.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/embed_oembed_html/
	 *
	 * @param mixed  $cache The cached HTML result, stored in post meta.
	 * @param string $url The attempted embed URL.
	 *
	 * @return mixed
	 */
	public function filter_youtube_oembed_nocookie( $cache, $url ) {
		if ( Features::is_active( Features::LAZY_YOUTUBE_PLAYER ) ) {
			return $this->new_youtube_filter( $cache, $url );
		}

		return $this->old_youtube_filter( $cache, $url );
	}

	/**
	 * Filter function for embed_oembed_html.
	 * Transform youtube embeds to youtube-nocookie.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/embed_oembed_html/
	 *
	 * @param mixed  $cache The cached HTML result, stored in post meta.
	 * @param string $url The attempted embed URL.
	 *
	 * @return mixed
	 */
	private function new_youtube_filter( $cache, $url ) {
		if ( ! empty( $url ) ) {
			if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {
				[ $youtube_id, $query_string ] = self::parse_youtube_url( $url );

				$style = "background-image: url('https://i.ytimg.com/vi/$youtube_id/hqdefault.jpg');";

				return '<lite-youtube style="' . $style . '" videoid="' . $youtube_id . '" params="' . $query_string . '"></lite-youtube>';
			}
		}

		return $cache;
	}

	/**
	 * Filter function for embed_oembed_html.
	 * Transform youtube embeds to youtube-nocookie.
	 *
	 * @see https://developer.wordpress.org/reference/hooks/embed_oembed_html/
	 *
	 * @param mixed  $cache The cached HTML result, stored in post meta.
	 * @param string $url The attempted embed URL.
	 *
	 * @return mixed
	 */
	private function old_youtube_filter( $cache, $url ) {
		if ( ! empty( $url ) ) {
			if ( strpos( $url, 'youtube.com' ) !== false || strpos( $url, 'youtu.be' ) !== false ) {

				$replacements = [
					'youtube.com'    => 'youtube-nocookie.com',
					'feature=oembed' => 'feature=oembed&rel=0',
				];

				$cache = str_replace( array_keys( $replacements ), array_values( $replacements ), $cache );
			}
		}

		return $cache;
	}

	/**
	 * Parse info out of a Youtube URL.
	 *
	 * @param string $url The embedded url.
	 *
	 * @return string[] The youtube ID and the query string.
	 */
	private static function parse_youtube_url( string $url ): ?array {
		// @see https://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id
		$re = '/(?im)\b(?:https?:\/\/)?(?:w{3}\.)?youtu(?:be)?\.(?:com|be)\/(?:(?:\??v=?i?=?\/?)|watch\?vi?=|watch\?.*?&v=|embed\/|)([A-Z0-9_-]{11})\S*(?=\s|$)/';
		preg_match_all( $re, $url, $matches, PREG_SET_ORDER );
		$youtube_id = $matches[0][1] ?? null;

		// For now just rel, but we can extract more from the url.
		$query_string = 'rel=0';

		return [ $youtube_id, $query_string ];
	}

	/**
	 * Add custom media metadata fields.
	 *
	 * @param array    $form_fields An array of fields included in the attachment form.
	 * @param \WP_Post $post The attachment record in the database.
	 *
	 * @return array Final array of form fields to use.
	 */
	public function add_image_attachment_fields_to_edit( $form_fields, $post ) {

		// Add a Credit field.
		$form_fields['credit_text'] = [
			'label' => __( 'Credit', 'planet4-master-theme-backend' ),
			'input' => 'text', // this is default if "input" is omitted.
			'value' => get_post_meta( $post->ID, '_credit_text', true ),
			'helps' => __( 'The owner of the image.', 'planet4-master-theme-backend' ),
		];

		return $form_fields;
	}

	/**
	 * Save custom media metadata fields
	 *
	 * @param \WP_Post $post        The $post data for the attachment.
	 * @param array    $attachment  The $attachment part of the form $_POST ($_POST[attachments][postID]).
	 *
	 * @return \WP_Post $post
	 */
	public function add_image_attachment_fields_to_save( $post, $attachment ) {
		if ( isset( $attachment['credit_text'] ) ) {
			update_post_meta( $post['ID'], '_credit_text', $attachment['credit_text'] );
		}

		return $post;
	}

	/**
	 * Override the Gutenberg core/image block render method output, To add credit field in it's caption text.
	 *
	 * @param array  $attributes    Attributes of the Gutenberg core/image block.
	 * @param string $content The image element HTML.
	 *
	 * @return string HTML content of image element with credit field in caption.
	 */
	public function p4_core_image_block_render( $attributes, $content ) {
		$image_id = isset( $attributes['id'] ) ? trim( str_replace( 'attachment_', '', $attributes['id'] ) ) : '';
		$meta     = get_post_meta( $image_id );

		if ( isset( $meta['_credit_text'] ) && ! empty( $meta['_credit_text'][0] ) ) {
			$image_credit = ' ' . $meta['_credit_text'][0];
			if ( false === strpos( $meta['_credit_text'][0], '©' ) ) {
				$image_credit = ' ©' . $image_credit;
			}

			if ( strpos( $content, '<figcaption>' ) !== false ) {
				$content = str_replace( '</figcaption>', esc_attr( $image_credit ) . '</figcaption>', $content );
			} else {
				$content = str_replace( '</figure>', '<figcaption>' . esc_attr( $image_credit ) . '</figcaption></figure>', $content );
			}
		}

		return $content;
	}

	/**
	 * Add callback function to Gutenberg core/image block.
	 */
	public function p4_register_core_image_block() {
		register_block_type(
			'core/image',
			[ 'render_callback' => [ $this, 'p4_core_image_block_render' ] ]
		);
	}

	/**
	 * Add callback function to Gutenberg core/image block.
	 *
	 * @param array $args Parameters given during block register.
	 *
	 * @return array Parameters of the block.
	 */
	public function register_core_blocks_callback( array $args ): array {
		if ( 'core/image' === $args['name'] ) {
			$args['render_callback'] = [ $this, 'p4_core_image_block_render' ];
		}

		return $args;
	}

	/**
	 * Show P4 team message on dashboard.
	 */
	public function show_dashboard_notice(): void {
		// Show only on dashboard.
		$screen = get_current_screen();
		if ( null === $screen || 'dashboard' !== $screen->id ) {
			return;
		}

		// Don't show a dismissed version.
		$last_notice = get_user_meta( get_current_user_id(), self::DASHBOARD_MESSAGE_KEY, true );
		if ( version_compare( self::DASHBOARD_MESSAGE_VERSION, $last_notice, '<=' ) ) {
			return;
		}

		// Don't show an empty message.
		$message = trim( $this->p4_message() );
		if ( empty( $message ) ) {
			return;
		}

		echo '<div id="p4-notice" class="notice notice-info is-dismissible">'
			. wp_kses_post( $message )
			. '</div>'
			. "<script>(function() {
				jQuery('#p4-notice').on('click', '.notice-dismiss', () => {
					jQuery.post(ajaxurl, {'action': 'dismiss_dashboard_notice'}, function(response) {
						jQuery('#p4-notice').hide();
					});
				});
			})();</script>
			";
	}

	/**
	 * A message from Planet4 team.
	 *
	 * Message title should be a <h2> tag.
	 * Message text should be written into <p> tags.
	 * Return an empty string if no message for this version.
	 *
	 * Version number DASHBOARD_MESSAGE_VERSION has to be incremented
	 * each time we add a new message.
	 *
	 * @todo Remove party message after over_date (PLANET-5782).
	 *
	 * @return string
	 */
	private function p4_message(): string {
		return '<h2>Welcome to the new P4 message board!</h2>
			<p>Did you already join the community on Slack? Here\'s the Planet 4 channels waiting for you in the Global Workspace 👇
				<ul>
					<li><span style="margin-right: 3px;">
						<a href="https://greenpeace.slack.com/archives/C014UMRC4AJ">#p4-general</a> 🌏</span>
						to learn, ask and share about P4, connect with the community and stay on top of all P4 news</li>
					<li><span style="margin-right: 3px;">
						<a href="https://greenpeace.slack.com/archives/C0151L0KKNX">#p4-dev</a> 🚀</span>
						for all coders, engineers and techies</li>
					<li><span style="margin-right: 3px;">
						<a href="https://greenpeace.slack.com/archives/C015E2TGLCR">#p4-design</a> 🎨</span>
						for all artists, creatives and visual wonders</li>
					<li><span style="margin-right: 3px;">
						<a href="https://greenpeace.slack.com/archives/C01672QUA0Z">#web-analytics</a> 📊</span>
						for all data ninjas</li>
					<li><span style="margin-right: 3px;">
						<a href="https://greenpeace.slack.com/archives/C014UMRD3T8">#p4-infra</a> ⚙️</span>
						for all Matrix architects</li>
				</ul>
			</p>';
	}

	/**
	 * Dismiss P4 notice of dashboard, by saving the last version read in user meta field.
	 *
	 * @uses wp_die()
	 */
	public function dismiss_dashboard_notice(): void {
		$user_id = get_current_user_id();
		if ( 0 === $user_id ) {
			wp_die( 'User not logged in.', 401 );
		}

		$res = update_user_meta(
			$user_id,
			self::DASHBOARD_MESSAGE_KEY,
			self::DASHBOARD_MESSAGE_VERSION
		);
		if ( false === $res ) {
			wp_die( 'User meta update failed.', 500 );
		}

		wp_die( 'Notice dismissed.', 200 );
	}
}
