<?php
/**
 * P4 Activator Class
 *
 * @package P4MT
 */

/**
 * Class P4_Activator.
 * The main class that has activation/deactivation hooks for planet4 master-theme.
 */
class P4_Activator {

	/**
	 * P4_Activator constructor.
	 */
	public function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks the activator functions.
	 */
	protected function hooks() {
		add_action( 'after_switch_theme', [ $this, 'run' ] );
	}

	/**
	 * Run activation functions.
	 */
	public function run() {
		$this->add_custom_roles_and_capabilities();
	}

	/**
	 * Add campaigner role and its capabilities.
	 */
	public function add_custom_roles_and_capabilities() {

		$campaigner = new P4_Campaigner();
		$campaigner->register_role_and_add_capabilities();
	}
}
