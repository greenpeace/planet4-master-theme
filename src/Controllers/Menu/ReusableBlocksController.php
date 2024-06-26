<?php
/**
 * Reusable Blocks class
 *
 * @package P4\MasterTheme\Controllers\Menu;
 */

namespace P4\MasterTheme\Controllers\Menu;

if ( ! class_exists( 'ReusableBlocksController' ) ) {

	/**
	 * Class ReusableBlocksController
	 */
	class ReusableBlocksController extends Controller {

		/**
		 * Post type name.
		 */
		const POST_TYPE = 'wp_block';

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) && current_user_can( 'edit_posts' ) ) {
				add_submenu_page(
					P4GBKS_PLUGIN_SLUG_NAME,
					__( 'All Reusable blocks', 'planet4-blocks-backend' ),
					__( 'All Reusable blocks', 'planet4-blocks-backend' ),
					'edit_posts',
					'edit.php?post_type=' . self::POST_TYPE
				);
			}
		}
	}
}
