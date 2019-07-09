<?php
/**
 * P4 Loader Class
 *
 * @package P4MT
 */

/**
 * Class P4_Loader.
 * Loads all necessary classes for Planet4 Master Theme.
 */
final class P4_Loader {
	/**
	 * A static instance of Loader.
	 *
	 * @var P4_Loader $instance
	 */
	private static $instance;
	/**
	 * Indexed array of all the classes/services that are needed.
	 *
	 * @var array $services
	 */
	private $services;
	/**
	 * Indexed array of all the classes/services that are used by Planet4.
	 *
	 * @var array $default_services
	 */
	private $default_services;

	/**
	 * Singleton creational pattern.
	 * Makes sure there is only one instance at all times.
	 *
	 * @param array $services The Controller services to inject.
	 *
	 * @return P4_Loader
	 */
	public static function get_instance( $services = [] ) : P4_Loader {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $services );
		}
		return self::$instance;
	}

	/**
	 * P4_Loader constructor.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function __construct( $services ) {
		$this->load_files();
		$this->load_services( $services );
	}

	/**
	 * Load required files.
	 */
	private function load_files() {
		try {
			// Class names need to be prefixed with P4 and should use capitalized words separated by underscores. Any acronyms should be all upper case.
			spl_autoload_register(
				function ( $class_name ) {
					if ( strpos( $class_name, 'P4_' ) !== false ) {
						$file_name = 'class-' . str_ireplace( [ 'P4\\', '_' ], [ '', '-' ], strtolower( $class_name ) );
						require_once __DIR__ . '/' . $file_name . '.php';
					}
				}
			);
		} catch ( \Exception $e ) {
			echo esc_html( $e->getMessage() );
		}
	}

	/**
	 * Inject dependencies.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function load_services( $services ) {

		$this->default_services = [
			'P4_Custom_Taxonomy',
			'P4_Post_Campaign',
			'P4_Settings',
			'P4_Post_Report_Controller',
			'P4_Cookies',
			'P4_Dev_Report',
			'P4_Master_Site',
		];

		if ( is_admin() ) {
			global $pagenow;

			// Load P4 Control Panel only on Dashboard page.
			$this->default_services[] = 'P4_Control_Panel';

			// Load P4 Metaboxes only when adding/editing a new Page/Post/Campaign.
			if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
				$this->default_services[] = 'P4_Metabox_Register';
			}

			// Load P4 Metaboxes only when adding/editing a new Page/Post/Campaign.
			if ( 'edit-tags.php' === $pagenow || 'term.php' === $pagenow ) {
				$this->default_services[] = 'P4_Campaigns';
			}

			// Load `P4_Campaign_Exporter` class on admin campaign listing page and campaign export only.
			if ( 'campaign' === filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING ) || 'export_data' === filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ) ) {
				$this->default_services[] = 'P4_Campaign_Exporter';
			}

			// Load `P4_Campaign_Importer` class on admin campaign import only.
			// phpcs:disable
			if ( 'wordpress' === filter_input( INPUT_GET, 'import', FILTER_SANITIZE_STRING ) ) {
				// phpcs:enable
				$this->default_services[] = 'P4_Campaign_Importer';
			}
		}

		$services = array_merge( $services, $this->default_services );
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
}
