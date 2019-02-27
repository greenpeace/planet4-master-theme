<?php
/**
 * Campaign Settings Exporter
 *
 * @package P4MT
 */

if ( ! class_exists( 'P4_Campaign_Exporter' ) ) {
	/**
	 * Class P4_Campaign_Exporter.
	 */
	class P4_Campaign_Exporter {
		/**
		 * AutoLoad Hooks
		 * */
		public function __construct() {
			add_action( 'admin_action_export_data', array( $this, 'single_post_export_data' ) );
			add_filter( 'post_row_actions', array( $this, 'single_post_export' ), 10, 2 );
			add_filter( 'page_row_actions', array( $this, 'single_post_export' ), 10, 2 );
			add_action( 'admin_footer-edit.php', array( $this, 'single_post_export_bulk' ) );
			add_action( 'load-edit.php', array( $this, 'single_post_export_bulk_action' ) );
			add_action( 'admin_head', array( $this, 'add_import_button' ) );
		}

		/**
		 * Main function
		 */
		public function single_post_export_data() {
			if ( ! ( isset( $_GET['post'] ) || isset( $_POST['post'] ) || ( isset( $_REQUEST['action'] ) ) ) ) {
				wp_die( 'No post to export has been supplied!' );
			}
			$post_id = ( isset( $_GET['post'] ) ? $_GET['post'] : $_POST['post'] );
			if ( ! empty( $post_id ) ) {
				include( get_template_directory() . '/exporter.php' );
			} else {
				wp_die( 'No post to export has been supplied!' );
			}
		}

		/**
		 * Export multiple data
		 */
		public function single_post_export_bulk() {
			if ( ! empty( $_GET['post_type'] ) &&
			'campaigns' == $_GET['post_type'] &&
			current_user_can( 'edit_posts' ) ) { ?>
			<script type="text/javascript">
				jQuery(document).ready(function () {
					jQuery('<option>').val('export').text('<?php _e( 'Export', 'planet4-master-theme-backend' ); ?>').appendTo("select[name='action']");
				});
			</script>
				<?php
			}
		}

		/**
		 * Export Bulk Action
		 */
		public function single_post_export_bulk_action() {
			$wp_list_table   = _get_list_table( 'WP_Posts_List_Table' );
			$action          = $wp_list_table->current_action();
			$allowed_actions = array( 'export' );
			if ( ! in_array( $action, $allowed_actions ) ) {
				return false;
			}
			switch ( $action ) {
				case 'export':
					$sendback = 'admin.php?action=export_data&post=' . join( ',', $_REQUEST['post'] );
					break;

				default:
					return;
			}
			wp_redirect( $sendback );
			exit();
		}

		/**
		 * Add Export Link
		 *
		 * @param object $actions Export Actions object.
		 * @param object $post Post object.
		 */
		public function single_post_export( $actions, $post ) {
			if ( ! empty( $_GET['post_type'] ) &&
				'campaigns' == $_GET['post_type'] &&
				current_user_can( 'edit_posts' ) ) {
				$export_url        = esc_url( admin_url( 'admin.php?action=export_data&amp;post=' . $post->ID ) );
				$actions['export'] = '<a href="' . $export_url . '" title="' . __( 'Export', 'planet4-master-theme-backend' ) . '" rel="permalink">' . __( 'Export', 'planet4-master-theme-backend' ) . '</a>';

			}

			return $actions;
		}

		/**
		 * Add Import Button
		 */
		public function add_import_button() {
			// phpcs:disable WordPress.WP.CapitalPDangit.Misspelled
			?>
			<script>
				jQuery(function(){
					jQuery("body.post-type-campaigns .wrap .page-title-action").after('<a href="admin.php?import=wordpress" class="page-title-action"><?php _e( 'Import', 'planet4-master-theme-backend' ); ?></a>');
				});
			</script>
			<?php
			// phpcs:enable
		}

	}
}
