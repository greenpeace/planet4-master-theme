<?php
/**
 * P4 Loader Class
 *
 * @package P4MT
 */

namespace P4\MasterTheme;

use P4_Campaign_Importer;
use P4_Campaigns;
use P4_Control_Panel;
use P4_Cookies;
use P4_Custom_Taxonomy;
use P4_Dev_Report;
use P4_Post_Archive;
use P4_Post_Campaign;
use P4_Post_Report_Controller;
use P4_Settings;
use WP_CLI;

/**
 * Class Loader.
 * Loads all necessary classes for Planet4 Master Theme.
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
	 * @return Loader
	 */
	public static function get_instance( $services = [] ) : Loader {
		if ( ! isset( self::$instance ) ) {
			self::$instance = new self( $services );
		}
		return self::$instance;
	}

	/**
	 * Loader constructor.
	 *
	 * @param array $services The dependencies to inject.
	 */
	private function __construct( $services ) {
		$this->load_files();
		$this->load_services( $services );
		$this->add_filters();
		$this->load_commands();
	}

	/**
	 * Load required files.
	 */
	private function load_files() {
		// Composer install might have happened inside the master-theme directory, instead of the app root.
		// Probably there's a better way to handle this, but for now let's try load both.
		if ( file_exists( __DIR__ . '/../vendor/autoload.php' ) ) {
			require_once __DIR__ . '/../vendor/autoload.php';
		} else {
			require_once __DIR__ . '/../../../../../vendor/autoload.php';
		}
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
			P4_Custom_Taxonomy::class,
			P4_Post_Campaign::class,
			P4_Post_Archive::class,
			P4_Settings::class,
			P4_Post_Report_Controller::class,
			P4_Cookies::class,
			P4_Dev_Report::class,
			MasterSite::class,
		];

		if ( is_admin() ) {
			global $pagenow;

			// Load P4 Control Panel only on Dashboard page.
			$this->default_services[] = P4_Control_Panel::class;

			// Load P4 Metaboxes only when adding/editing a new Page/Post/Campaign.
			if ( 'post-new.php' === $pagenow || 'post.php' === $pagenow ) {
				$this->default_services[] = MetaboxRegister::class;
				add_action(
					'cmb2_save_field_p4_campaign_name',
					[ MetaboxRegister::class, 'save_global_project_id' ],
					10,
					3
				);
			}

			// Load P4 Metaboxes only when adding/editing a new Page/Post/Campaign.
			if ( 'edit-tags.php' === $pagenow || 'term.php' === $pagenow ) {
				$this->default_services[] = P4_Campaigns::class;
			}

			// Load `P4_Campaign_Exporter` class on admin campaign listing page and campaign export only.
			if ( 'campaign' === filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING ) || 'export_data' === filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ) ) {
				$this->default_services[] = CampaignExporter::class;
			}

			// Load `P4_Campaign_Importer` class on admin campaign import only.
			// phpcs:disable
			if ( 'wordpress' === filter_input( INPUT_GET, 'import', FILTER_SANITIZE_STRING ) ) {
				// phpcs:enable
				$this->default_services[] = P4_Campaign_Importer::class;
			}
		}

		// Run P4_Activator after theme switched to planet4-master-theme or a planet4 child theme.
		if ( get_option( 'theme_switched' ) ) {
			$this->default_services[] = Activator::class;
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

	/**
	 * Add some filters.
	 *
	 * @return void
	 */
	private function add_filters(): void {
		add_filter( 'pre_delete_post', [ $this, 'do_not_delete_autosave' ], 1, 3 );
	}

	/**
	 * Registers WP_CLI commands.
	 */
	public function load_commands() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}

		$command = static function ( $args, $assoc_args ) {
			Activator::run();
		};

		WP_CLI::add_command( 'p4-run-activator', $command );
	}

	/**
	 * Due to a bug in WordPress core the "autosave revision" of a post is created and deleted all of the time.
	 * This is pretty pointless and makes it impractical to add any post meta to that revision.
	 * The logic was probably that some space could be saved it is can be determined that the autosave doesn't differ
	 * from the current post content. However that advantage doesn't weigh up to the overhead of deleting the record and
	 * inserting it again, each time burning through another id of the posts table.
	 *
	 * @see https://core.trac.wordpress.org/ticket/49532
	 *
	 * @param null $delete Whether to go forward with the delete (sic, see original filter where it is null initally, not used here).
	 * @param null $post Post object.
	 * @param null $force_delete Is true when post is not trashed but deleted permanently (always false for revisions but they are deleted anyway).
	 *
	 * @return bool|null If the filter returns anything else than null the post is not deleted.
	 */
	public function do_not_delete_autosave( $delete = null, $post = null, $force_delete = null ): ?bool {
		if (
			$force_delete
			|| ( isset( $_GET['action'] ) && 'delete' === $_GET['action'] ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ( isset( $_GET['delete_all'] ) ) // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			|| ! preg_match( '/autosave-v\d+$/', $post->post_name ) ) {

			return null;
		}

		return false;
	}

	/**
	 * @param string $rel_path Relative path to the file.
	 * @return int timestamp of file creation
	 */
	public static function theme_file_ver( string $rel_path ): int {
		$filepath = trailingslashit( get_template_directory() ) . $rel_path;
		$ctime    = filectime( $filepath );
		if ( $ctime ) {
			return $ctime;
		}

		return time();
	}
}

class_alias( Loader::class, 'P4_Loader' );
