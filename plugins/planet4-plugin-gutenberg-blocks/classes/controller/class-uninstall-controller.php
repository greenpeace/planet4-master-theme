<?php
/**
 * Uninstall class, containing functions to clean up plugin's data after removal.
 *
 * @package P4GBKS
 * @since 0.1.0
 */

namespace P4GBKS\Controllers;

/**
 * Class Uninstall_Controller
 *
 * @package P4GBKS\Controllers
 */
class Uninstall_Controller {

	/**
	 * Initialize uninstaller
	 */
	public function __construct() {

		// Exit if accessed directly.
		if ( ! defined( 'ABSPATH' ) ) {
			$this->exit_uninstaller();
		}
		// Not uninstalling.
		if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
			$this->exit_uninstaller();
		}
		// Not uninstalling.
		if ( ! WP_UNINSTALL_PLUGIN ) {
			$this->exit_uninstaller();
		}
		// Clean any options that were created by the plugin.
		self::clean_options();
	}

	/**
	 * Cleanup options
	 *
	 * Deletes Planet4 - Blocks options and transients.
	 *
	 * @return void
	 */
	protected static function clean_options() {
		// Delete options.
		delete_option( 'p4gbks_main_settings' );
		delete_option( 'p4gbks_pages_settings' );
	}

	/**
	 * Exit uninstaller
	 *
	 * Gracefully exit the uninstaller if we should not be here
	 *
	 * @return void
	 */
	protected function exit_uninstaller() {
		status_header( 404 );
		exit;

	}
}
