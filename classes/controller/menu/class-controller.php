<?php
/**
 * Base class
 *
 * @package P4BKS
 * @since 0.1.0
 */

namespace P4GBKS\Controllers\Menu;

use P4GBKS\Views\View;

if ( ! class_exists( 'Controller' ) ) {

	/**
	 * Class Controller
	 *
	 * This class will control all the main functions of the plugin.
	 */
	abstract class Controller {

		/**
		 * View object
		 *
		 * @var View $view
		 */
		protected $view;


		/**
		 * Creates the plugin's controller object.
		 * Avoid putting hooks inside the constructor, to make testing easier.
		 *
		 * @param View $view The view object.
		 */
		public function __construct( View $view ) {
			$this->view = $view;
		}

		/**
		 * Hooks the method that Creates the menu item for the current controller.
		 */
		public function load() {
			add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		}

		/**
		 * Validates and sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return mixed Array if validation is ok, false if validation fails.
		 */
		public function valitize( $settings ) {
			if ( $this->validate( $settings ) ) {
				$this->sanitize( $settings );
				return $settings;
			} else {
				return $settings;
			}
		}

		/**
		 * Validates the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin.
		 *
		 * @return bool
		 */
		abstract public function validate( $settings ) : bool;

		/**
		 * Sanitizes the settings input.
		 *
		 * @param array $settings The associative array with the settings that are registered for the plugin (Call by Reference).
		 */
		abstract public function sanitize( &$settings );
	}
}
