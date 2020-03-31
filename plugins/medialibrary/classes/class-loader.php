<?php

namespace P4ML;

if ( ! class_exists( 'Loader' ) ) {

	/**
	 * Class Loader
	 *
	 * This class checks requirements and if all are met then it hooks the plugin.
	 */
	final class Loader {

		/** @var Loader $instance */
		private static $instance;
		/** @var array $services */
		private $services;
		/** @var string $required_php */
		private $required_php = P4ML_REQUIRED_PHP;
		/** @var array $required_plugins */
		private $required_plugins = P4ML_REQUIRED_PLUGINS;


		/**
		 * Singleton creational pattern.
		 * Makes sure there is only one instance at all times.
		 *
		 * @param array  $services The Controller services to inject.
		 * @param string $view_class The View class name.
		 *
		 * @return Loader
		 */
		public static function get_instance( $services = array(), $view_class ): Loader {
			! isset( self::$instance ) and self::$instance = new self( $services, $view_class );

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
		private function __construct( $services = array(), $view_class ) {
			$this->services = $services;
			$view           = new $view_class();

			if ( $this->services ) {
				foreach ( $this->services as $service ) {
					( new $service( $view ) )->load();
				}
			}
			$this->check_requirements();
		}

		/**
		 * Hooks the plugin.
		 */
		private function hook_plugin() {
			add_action( 'admin_menu', array( $this, 'load_i18n' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'load_admin_assets' ) );
			// Provide hook for other plugins.
			do_action( 'p4ml_action_loaded' );
		}

		/**
		 * Checks plugin requirements.
		 * If requirements are met then hook the plugin.
		 */
		private function check_requirements() {

			if ( is_admin() ) {         // If we are on the admin panel.
				// Run the version check. If it is successful, continue with hooking under 'init' the initialization of this plugin.
				if ( $this->check_required_php() ) {
					if ( $this->check_required_plugins( $plugin ) ) {
						$this->hook_plugin();
					} else {
						deactivate_plugins( P4ML_PLUGIN_BASENAME );
						wp_die(
							'<div class="error fade">' .
							'<u>' . esc_html__( 'Plugin Requirements Error!', 'planet4-medialibrary' ) . '</u><br /><br />' . esc_html( P4ML_PLUGIN_NAME ) . esc_html__( ' requires a newer version of the following plugin.', 'planet4-medialibrary' ) . '<br />' .
							'<br/>' . esc_html__( 'Minimum required version of ', 'planet4-medialibrary' ) . esc_html( $plugin['Name'] ) . ': <strong>' . esc_html( $plugin['min_version'] ) . '</strong>' .
							'<br/>' . esc_html__( 'Installed version of ', 'planet4-medialibrary' ) . esc_html( $plugin['Name'] ) . ': <strong>' . esc_html( $plugin['Version'] ) . '</strong>' .
							'</div>',
							'Plugin Requirements Error',
							array(
								'response'  => \WP_Http::OK,
								'back_link' => true,
							)
						);
					}
				} else {
					deactivate_plugins( P4ML_PLUGIN_BASENAME );
					wp_die(
						'<div class="error fade">' .
						'<u>' . esc_html__( 'Plugin Requirements Error!', 'planet4-medialibrary' ) . '</u><br /><br />' . esc_html( P4ML_PLUGIN_NAME . __( ' requires a newer version of PHP.', 'planet4-medialibrary' ) ) . '<br />' .
						'<br/>' . esc_html__( 'Minimum required version of PHP: ', 'planet4-medialibrary' ) . '<strong>' . esc_html( $this->required_php ) . '</strong>' .
						'<br/>' . esc_html__( 'Running version of PHP: ', 'planet4-medialibrary' ) . '<strong>' . esc_html( phpversion() ) . '</strong>' .
						'</div>',
						'Plugin Requirements Error',
						array(
							'response'  => \WP_Http::OK,
							'back_link' => true,
						)
					);
				}
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
		 * @param array $plugin Will contain information for those plugins whose requirements are not met.
		 *
		 * @return bool true if version check passed or false otherwise.
		 */
		private function check_required_plugins( &$plugin ): bool {
			$required_plugins = $this->required_plugins;

			if ( is_array( $required_plugins ) && $required_plugins ) {
				foreach ( $required_plugins as $required_plugin ) {
					$plugin_data = get_plugin_data( WP_PLUGIN_DIR . '/' . $required_plugin['rel_path'] );

					if ( ! is_plugin_active( $required_plugin['rel_path'] ) ||
						 ! version_compare( $plugin_data['Version'], $required_plugin['min_version'], '>=' ) ) {
						$plugin = array_merge( $plugin_data, $required_plugin );

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
		public function load_admin_assets( $hook ) {

		}

		/**
		 * Load internationalization (i18n) for this plugin.
		 * References: http://codex.wordpress.org/I18n_for_WordPress_Developers
		 */
		public function load_i18n() {
			load_plugin_textdomain( 'planet4-medialibrary', false, P4ML_PLUGIN_DIRNAME . '/languages/' );
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

} else {
	deactivate_plugins( P4ML_PLUGIN_BASENAME );
	wp_die(
		'<div class="error fade">' .
		'<u>' . esc_html__( 'Plugin Conflict Error!', 'planet4-medialibrary' ) . '</u><br /><br />' . esc_html__( 'Class Loader already exists.', 'planet4-medialibrary' ) . '<br />' .
		'</div>',
		'Plugin Conflict Error',
		array(
			'response'  => \WP_Http::OK,
			'back_link' => true,
		)
	);
}
