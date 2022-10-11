<?php
/**
 * Settings class
 *
 * @package P4BKS\Controllers\Menu
 * @since 1.40.0
 */

namespace P4GBKS\Controllers\Menu;

if ( ! class_exists( 'Settings_Controller' ) ) {

	/**
	 * Class Settings_Controller
	 */
	class Settings_Controller extends Controller {

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) && current_user_can( 'edit_posts' ) ) {

				add_menu_page(
					__( 'Blocks', 'planet4-blocks-backend' ),
					__( 'Blocks', 'planet4-blocks-backend' ),
					'edit_posts',
					P4GBKS_PLUGIN_SLUG_NAME,
					null,
					'dashicons-layout'
				);
			}
		}
	}
}
