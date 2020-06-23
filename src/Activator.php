<?php
/**
 * P4 Activator Class
 *
 * @package P4MT
 */

namespace P4\MasterTheme;

use P4_Campaigner;

/**
 * Class P4_Activator.
 * The main class that has activation/deactivation hooks for planet4 master-theme.
 */
class Activator {

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
		add_action( 'after_switch_theme', [ self::class, 'run' ] );
	}

	/**
	 * Run activation functions.
	 */
	public static function run(): void {
		P4_Campaigner::register_role_and_add_capabilities();
	}
}
