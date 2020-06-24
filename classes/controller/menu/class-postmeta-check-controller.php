<?php
/**
 * Post meta duplicate records check class
 *
 * @package P4BKS\Controllers\Menu
 */

namespace P4GBKS\Controllers\Menu;

use P4GBKS\Command\Duplicated_Postmeta;

/**
 * Class Postmeta_Check_Controller
 */
class Postmeta_Check_Controller extends Controller {
	/**
	 * Create menu/submenu entry.
	 */
	public function create_admin_menu() {
		$current_user = wp_get_current_user();
		if ( in_array( 'administrator', $current_user->roles, true ) ) {
			add_submenu_page(
				P4GBKS_PLUGIN_SLUG_NAME,
				__( 'Postmeta Check', 'planet4-blocks-backend' ),
				__( 'Postmeta Check', 'planet4-blocks-backend' ),
				'manage_options',
				'postmeta_report',
				[ $this, 'postmeta_check' ]
			);
		}
	}

	/**
	 * Handle form submit.
	 *
	 * @param mixed[] $data The form data.
	 */
	public function handle_submit( &$data ) {
		$remove_duplicate_postmeta = filter_input( INPUT_POST, 'delete_duplicate_postmeta', FILTER_SANITIZE_NUMBER_INT );
		if ( 'POST' === $_SERVER['REQUEST_METHOD'] && $remove_duplicate_postmeta ) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
			try {
				$deleted_rows = Duplicated_Postmeta::remove();
			} catch ( \Error $e ) {
				$data['message'] = $e->getMessage();
			} catch ( \Exception $e ) {
				$data['message'] = __( 'Exception: ', 'planet4-blocks-backend' ) . $e->getMessage();
			}

			if ( $deleted_rows ) {
				// translators: %d = The duplicate postmeta count.
				$data['message'] = sprintf( __( 'Remove %d duplicate postmeta records successfully.', 'planet4-blocks-backend' ), $deleted_rows );
			} else {
				$data['message'] = __( 'No whitelisted duplicate postmeta records found.', 'planet4-blocks-backend' );
			}
		}
	}

	/**
	 * Render the admin page with duplicate postmeta details.
	 */
	public function postmeta_check() {
		$data = [];

		$this->handle_submit( $data );
		$data['duplicate_postmeta'] = Duplicated_Postmeta::detect();
		$data['postmeta_keys']      = Duplicated_Postmeta::META_KEY_LIST;

		$this->view->block( 'duplicate-postmeta-report', $data, 'twig', '' );
	}
}
