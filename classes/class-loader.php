<?php
/**
 * Loader class
 *
 * @package P4GBKS
 * @since 0.1.0
 */

namespace P4GBKS;

use WP_CLI;

/**
 * Class Loader
 *
 * Loads required files.
 * Starts services.
 * Loads commands.
 * Checks requirements and if all are met.
 * Hooks plugin and loads assets.
 */
final class Loader {

	/**
	 * A static instance of Loader.
	 *
	 * @var Loader $instance
	 */
	private static $instance;
	/**
	 * Indexed array of all the classes/services that are needed.
	 *
	 * @var array $services
	 */
	private $services;
	/**
	 * An instance of the View class.
	 *
	 * @var Views\View $view
	 */
	private $view;
	/**
	 * Required version of php.
	 *
	 * @var string $required_php
	 */
	private $required_php = P4GBKS_REQUIRED_PHP;
	/**
	 * Array with all required plugins and their required versions.
	 *
	 * @var array $required_plugins
	 */
	private $required_plugins = P4GBKS_REQUIRED_PLUGINS;

	/**
	 * Block instances
	 *
	 * @var $blocks
	 */
	private $blocks;

	/**
	 * Singleton creational pattern.
	 * Makes sure there is only one instance at all times.
	 *
	 * @param array  $services The Controller services to inject.
	 * @param string $view_class The View class name.
	 *
	 * @return Loader
	 */
	public static function get_instance( $services, $view_class ) : Loader {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $services, $view_class );
		}

		return self::$instance;
	}

	/**
	 * Creates the plugin's loader object.
	 * Checks requirements and if its ok it hooks the hook_plugin method on the 'init' action which fires
	 * after WordPress has finished loading but before any headers are sent.
	 * Most of WP is loaded at this stage (but not all) and the user is authenticated.
	 *
	 * @param array  $services The Controller services to inject.
	 * @param string $view_class The View class name.
	 */
	private function __construct( $services, $view_class ) {

		$this->load_files();
		$this->load_services( $services, $view_class );
		$this->load_commands();
		$this->check_requirements();

		// Load Blocks.
		$this->blocks = [
			new Blocks\Articles(),
			new Blocks\CarouselHeader(),
			new Blocks\Columns(),
			new Blocks\Cookies(),
			new Blocks\Counter(),
			new Blocks\Covers(),
			new Blocks\Gallery(),
			new Blocks\Happypoint(),
			new Blocks\Media(),
			new Blocks\SocialMedia(),
			new Blocks\SplitTwoColumns(),
			new Blocks\SubMenu(),
			new Blocks\TakeActionBoxout(),
			new Blocks\Timeline(),
		];
	}

	/**
	 * Load required files. The plugins namespaces should:
	 * a. include P4GBKS string
	 * b. follow the names of the sub-directories of the current __DIR__ (classes/)
	 *    - if not, then proper replacements should be added like below
	 */
	private function load_files() {
		try {
			spl_autoload_register(
				function ( $class_name ) {
					if ( false !== strpos( $class_name, 'P4GBKS' ) ) {
						$class_name_parts = explode( '\\', $class_name );
						$real_class_name  = array_pop( $class_name_parts );
						$file_name        = 'class-' . str_ireplace( '_', '-', strtolower( $real_class_name ) );

						$namespace = implode( '\\', $class_name_parts );
						$path      = str_ireplace(
							[ 'P4GBKS', 'Blocks', 'Controllers', 'Views', '_', '\\' ],
							[ '', 'blocks', 'controller', 'view', '-', '/' ],
							strtolower( $namespace )
						);
						require_once __DIR__ . '/' . $path . '/' . $file_name . '.php';
					}
				}
			);
		} catch ( \Exception $e ) {
			echo esc_html( $e->getMessage() );
		}
	}

	/**
	 * Loads all shortcake blocks registered from within this plugin.
	 *
	 * @param array  $services The Controller services to inject.
	 * @param string $view_class The View class name.
	 */
	public function load_services( $services, $view_class ) {
		$this->services = $services;
		$this->view     = new $view_class();

		if ( $this->services ) {
			foreach ( $this->services as $service ) {
				( new $service( $this->view ) )->load();
			}
		}
	}

	/**
	 * Registers commands for Blocks plugin.
	 */
	public function load_commands() {
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			try {
				WP_CLI::add_command(
					'p4-gblocks',
					'P4GBKS\Command\Controller'
				);
			} catch ( \Exception $e ) {
				WP_CLI::log( 'Exception: ' . $e->getMessage() );
			}
		}
	}

	/**
	 * Hooks the plugin.
	 */
	private function hook_plugin() {
		add_action( 'admin_menu', [ $this, 'load_i18n' ] );
		// Load the editor scripts only enqueuing editor scripts while in context of the editor.
		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_scripts' ] );

		// Setup image sizes.
		add_action( 'admin_init', [ $this, 'setup_image_sizes' ] );

		// Set color palette.
		add_action( 'admin_init', [ $this, 'set_color_palette' ] );

		// Register a block category.
		add_filter( 'block_categories', [ $this, 'register_block_category' ], 10, 2 );
		// Provide hook for other plugins.
		do_action( 'p4gbks_plugin_loaded' );
	}

	/**
	 * Checks plugin requirements.
	 * If requirements are met then hook the plugin.
	 */
	private function check_requirements() {

		if ( is_admin() ) {         // If we are on the admin panel.
			// Run the version check. If it is successful, continue with hooking under 'init' the initialization of this plugin.
			if ( $this->check_required_php() ) {
				$plugins = [
					'not_found'   => [],
					'not_updated' => [],
				];
				if ( $this->check_required_plugins( $plugins ) ) {
					$this->hook_plugin();
				} elseif ( $plugins['not_found'] || $plugins['not_updated'] ) {

					deactivate_plugins( P4GBKS_PLUGIN_BASENAME );
					$count   = 0;
					$message = '<div class="error fade">' .
							'<u>' . esc_html( P4GBKS_PLUGIN_NAME ) . ' > ' . esc_html__( 'Requirements Error(s)', 'planet4-blocks-backend' ) . '</u><br /><br />';

					foreach ( $plugins['not_found'] as $plugin ) {
						$message .= '<br/><strong>' . ( ++ $count ) . '. ' . esc_html( $plugin['Name'] ) . '</strong> ' . esc_html__( 'plugin needs to be installed and activated.', 'planet4-blocks-backend' ) . '<br />';
					}
					foreach ( $plugins['not_updated'] as $plugin ) {
						$message .= '<br/><strong>' . ( ++ $count ) . '. ' . esc_html( $plugin['Name'] ) . '</strong><br />' .
									esc_html__( 'Minimum version ', 'planet4-blocks-backend' ) . '<strong>' . esc_html( $plugin['min_version'] ) . '</strong>' .
									'<br/>' . esc_html__( 'Current version ', 'planet4-blocks-backend' ) . '<strong>' . esc_html( $plugin['Version'] ) . '</strong><br />';
					}

					$message .= '</div><br />';
					wp_die(
						$message, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'Plugin Requirements Error',
						[
							'response'  => \WP_Http::OK, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
							'back_link' => true,
						]
					);
				}
			} else {
				deactivate_plugins( P4GBKS_PLUGIN_BASENAME );
				wp_die( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
					'<div class="error fade">' .
					'<strong>' . esc_html__( 'PHP Requirements Error', 'planet4-blocks-backend' ) . '</strong><br /><br />' . esc_html( P4GBKS_PLUGIN_NAME . __( ' requires a newer version of PHP.', 'planet4-blocks-backend' ) ) . '<br />' .
					'<br/>' . esc_html__( 'Minimum required version of PHP: ', 'planet4-blocks-backend' ) . '<strong>' . esc_html( $this->required_php ) . '</strong>' .
					'<br/>' . esc_html__( 'Running version of PHP: ', 'planet4-blocks-backend' ) . '<strong>' . esc_html( phpversion() ) . '</strong>' .
					'</div>',
					'Plugin Requirements Error',
					[
						'response'  => \WP_Http::OK, // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
						'back_link' => true,
					]
				);
			}
		} else {
			add_action( 'plugins_loaded', [ $this, 'load_i18n' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_public_assets' ] );
			add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_campaigns_assets' ] );
		}
	}

	/**
	 * Check if the server's php version is less than the required php version.
	 *
	 * @return bool true if version check passed or false otherwise.
	 */
	private function check_required_php(): bool {
		return version_compare( phpversion(), $this->required_php, '>=' );
	}

	/**
	 * Check if the version of a plugin is less than the required version.
	 *
	 * @param array $plugins Will contain information for those plugins whose requirements are not met.
	 *
	 * @return bool true if version check passed or false otherwise.
	 */
	private function check_required_plugins( &$plugins ): bool {
		$required_plugins = $this->required_plugins;

		if ( is_array( $required_plugins ) && $required_plugins ) {
			foreach ( $required_plugins as $required_plugin ) {
				$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $required_plugin['rel_path'] );

				if ( ! is_plugin_active( $required_plugin['rel_path'] ) ) {
					array_push( $plugins['not_found'], array_merge( $plugin_data, $required_plugin ) );
				} elseif ( ! version_compare( $plugin_data['Version'], $required_plugin['min_version'], '>=' ) ) {
					array_push( $plugins['not_updated'], array_merge( $plugin_data, $required_plugin ) );
				}
			}
			foreach ( $plugins as $plugin ) {
				if ( is_array( $plugin ) && count( $plugin ) > 0 ) {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * Load assets only on the admin pages of the plugin.
	 *
	 * @param string $hook The slug name of the current admin page.
	 */
	public function enqueue_editor_scripts( $hook ) {

		wp_enqueue_style( 'wp-components' );

		// These styles from the master theme are enqueued on the frontend
		// but not in the admin side.
		wp_enqueue_style(
			'p4gbks_admin_style',
			P4GBKS_PLUGIN_URL . 'editorStyle.min.css', // - Bundled CSS for the blocks
			[],
			'0.2'
		);

		wp_enqueue_script(
			'p4gbks_admin_script',
			P4GBKS_PLUGIN_URL . 'admin/js/editor.js',
			[],
			'0.1',
			true
		);

		// Enqueue editor script for all Blocks in this Plugin.
		wp_enqueue_script(
			'planet4-blocks-script',                       // - Script handler
			P4GBKS_PLUGIN_URL . 'assets/build/editorIndex.js',                                     // - Bundled JS for the editor
			[
				'wp-blocks',      // - Helpers for registering blocks
				'wp-components',  // - Wordpress components
				'wp-element',     // - WP React wrapper
				'wp-data',        // - WP data helpers
				'wp-i18n',        // - Exports the __() function
				'wp-editor',
			],
			'0.1.8',
			true
		);

		// Variables exposed from PHP to JS,
		// WP calls this "localizing a script"...
		$option_values = get_option( 'planet4_options' );

		$reflection_vars = [
			'home'            => P4GBKS_PLUGIN_URL . '/public/',
			'planet4_options' => $option_values,
		];
		wp_localize_script( 'planet4-blocks-script', 'p4ge_vars', $reflection_vars );
	}

	/**
	 * Load assets for the frontend.
	 */
	public function enqueue_public_assets() {
		// plugin-blocks assets.
		$css_blocks_creation = filectime( P4GBKS_PLUGIN_DIR . '/assets/build/style.min.css' );

		// Add master theme's main css as dependency for blocks css.
		wp_enqueue_style(
			'plugin-blocks',
			P4GBKS_PLUGIN_URL . 'style.min.css',
			[
				'bootstrap',
				'slick',
				'parent-style',
			],
			$css_blocks_creation
		);

		wp_enqueue_script( 'post_action', P4GBKS_PLUGIN_URL . 'public/js/post_action.js', [ 'jquery' ], '0.1', true );
	}

	/**
	 * Load assets for the frontend.
	 */
	public function enqueue_campaigns_assets() {
		// plugin-blocks assets.
		$css_campaigns_creation = filectime( P4GBKS_PLUGIN_DIR . '/assets/build/campaigns.min.css' );

		$post_type = get_post_type();

		if ( 'campaign' === $post_type ) {
			wp_enqueue_style(
				'campaigns',
				P4GBKS_PLUGIN_URL . 'campaigns.min.css',
				[
					'plugin-blocks',
				],
				$css_campaigns_creation
			);
		}
	}

	/**
	 * Add sizes to the image size selector in the Image block's settings sidebar
	 */
	public function setup_image_sizes() {
		// These array keys should match the image sizes added
		// through add_image_size (search for them in the master theme).
		$custom_sizes['articles-medium-large'] = 'Articles Medium Large';
		$custom_sizes['retina-large']          = 'Retina Large';
		add_filter(
			'image_size_names_choose',
			function( $sizes ) use ( $custom_sizes ) {
				return array_merge( $sizes, $custom_sizes );
			}
		);
	}

	/**
	 * Load internationalization (i18n) for this plugin.
	 * References: http://codex.wordpress.org/I18n_for_WordPress_Developers
	 */
	public function load_i18n() {
		load_plugin_textdomain( 'planet4-blocks', false, P4GBKS_PLUGIN_DIRNAME . '/languages/' );
		load_plugin_textdomain( 'planet4-blocks-backend', false, P4GBKS_PLUGIN_DIRNAME . '/languages/' );
	}

	/**
	 * Registers a new category for our blocks
	 *
	 * @param array $categories Blocks categories.
	 *
	 * @return array
	 */
	public function register_block_category( $categories ) {
		// get_block_categories method at wp-admin/includes/post.php has some hardcoded (!!!) default categories
		// index 0 holds common blocks menu entry data, so it seems safe to do the bellow array operations.
		$common = [
			$categories[0],
		];

		$categories[0] = [
			'slug'  => 'planet4-blocks',
			'title' => __( 'Planet 4 Blocks', 'planet4-blocks' ),
		];

		return array_merge(
			$common,
			$categories
		);
	}


	/**
	 * Registers a new color palette for all native blocks
	 */
	public function set_color_palette() {
		add_theme_support(
			'editor-color-palette',
			[
				[
					'name'  => __( 'Dark Shade Black', 'planet4-blocks-backend' ),
					'slug'  => 'dark-shade-black',
					'color' => '#1a1a1a',
				],
				[
					'name'  => __( 'Grey 60%', 'planet4-blocks-backend' ),
					'slug'  => 'grey-60',
					'color' => '#666666',
				],
				[
					'name'  => __( 'Grey 40%', 'planet4-blocks-backend' ),
					'slug'  => 'grey-40',
					'color' => '#999999',
				],
				[
					'name'  => __( 'Grey 20%', 'planet4-blocks-backend' ),
					'slug'  => 'grey-20',
					'color' => '#cccccc',
				],
				[
					'name'  => __( 'Grey 10%', 'planet4-blocks-backend' ),
					'slug'  => 'grey-10',
					'color' => '#e5e5e5',
				],
				[
					'name'  => __( 'Grey', 'planet4-blocks-backend' ),
					'slug'  => 'grey',
					'color' => '#333333',
				],
				[
					'name'  => __( 'Green', 'planet4-blocks-backend' ),
					'slug'  => 'green',
					'color' => '#003300',
				],
				[
					'name'  => __( 'Green 80%', 'planet4-blocks-backend' ),
					'slug'  => 'green-80',
					'color' => '#1b4a1b',
				],
				[
					'name'  => __( 'GP Green', 'planet4-blocks-backend' ),
					'slug'  => 'gp-green',
					'color' => '#66cc00',
				],
				[
					'name'  => __( 'Dark Tiber', 'planet4-blocks-backend' ),
					'slug'  => 'dark-tiber',
					'color' => '#052a30',
				],
				[
					'name'  => __( 'Genoa', 'planet4-blocks-backend' ),
					'slug'  => 'genoa',
					'color' => '#186a70',
				],
				[
					'name'  => __( 'Inch Worm', 'planet4-blocks-backend' ),
					'slug'  => 'inch-worm',
					'color' => '#a7e021',
				],
				[
					'name'  => __( 'X Dark Blue', 'planet4-blocks-backend' ),
					'slug'  => 'x-dark-blue',
					'color' => '#042233',
				],
				[
					'name'  => __( 'All Ports', 'planet4-blocks-backend' ),
					'slug'  => 'allports',
					'color' => '#007799',
				],
				[
					'name'  => __( 'Spray', 'planet4-blocks-backend' ),
					'slug'  => 'spray',
					'color' => '#86eee7',
				],
				[
					'name'  => __( 'Dark Blue', 'planet4-blocks-backend' ),
					'slug'  => 'dark-blue',
					'color' => '#074365',
				],
				[
					'name'  => __( 'Blue', 'planet4-blocks-backend' ),
					'slug'  => 'blue',
					'color' => '#2077bf',
				],
				[
					'name'  => __( 'Blue 60%', 'planet4-blocks-backend' ),
					'slug'  => 'blue-60',
					'color' => '#63bbfd',
				],
				[
					'name'  => __( 'Crimson', 'planet4-blocks-backend' ),
					'slug'  => 'crimson',
					'color' => '#e51538',
				],
				[
					'name'  => __( 'Orange Hover', 'planet4-blocks-backend' ),
					'slug'  => 'orange-hover',
					'color' => '#ee562d',
				],
				[
					'name'  => __( 'Yellow', 'planet4-blocks-backend' ),
					'slug'  => 'yellow',
					'color' => '#ffd204',
				],
			]
		);

		// Disable custom color option.
		add_theme_support( 'disable-custom-colors' );
	}

	/**
	 * Make clone magic method private, so nobody can clone instance.
	 */
	private function __clone() {
	}

	/**
	 * Make wakeup magic method private, so nobody can unserialize instance.
	 */
	private function __wakeup() {
	}
}

