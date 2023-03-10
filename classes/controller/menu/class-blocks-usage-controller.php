<?php
/**
 * Blocks Usage class
 *
 * @package P4BKS\Controllers\Menu
 * @since 1.40.0
 */

namespace P4GBKS\Controllers\Menu;

use P4\MasterTheme\SqlParameters;
use P4GBKS\Search\Block\BlockUsage;
use P4GBKS\Search\Block\BlockUsageTable;
use P4GBKS\Search\Block\BlockUsageApi;
use P4GBKS\Search\Block\Query\Parameters as BlockParameters;
use P4GBKS\Search\Pattern\Query\Parameters as PatternParameters;
use P4GBKS\Search\Pattern\PatternUsage;
use P4GBKS\Search\Pattern\PatternUsageTable;
use P4GBKS\Search\Pattern\PatternUsageApi;
use WP_Block_Type_Registry;
use WP_Block_Patterns_Registry;

if ( ! class_exists( 'Blocks_Usage_Controller' ) ) {

	/**
	 * Class Blocks_Usage_Controller
	 */
	class Blocks_Usage_Controller extends Controller {
		/**
		 * Blocks_Usage_Controller constructor.
		 *
		 * @param View $view The view object.
		 */
		public function __construct( $view ) {
			parent::__construct( $view );
			$this->hooks();
		}

		/**
		 * Class hooks.
		 */
		private function hooks() {
			add_action( 'rest_api_init', [ $this, 'plugin_blocks_report_register_rest_route' ] );
			BlockUsageTable::set_hooks();
			PatternUsageTable::set_hooks();
		}

		/**
		 * Register API route for report of blocks usage in pages/posts.
		 */
		public function plugin_blocks_report_register_rest_route() {
			register_rest_route(
				'plugin_blocks/v3',
				'/plugin_blocks_report/',
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'plugin_blocks_report_rest_api' ],
					'permission_callback' => '__return_true',
				]
			);
		}

		/**
		 * Generates blocks/pages report.
		 */
		public function plugin_blocks_report_rest_api() {
			global $wpdb;

			$types = \get_post_types(
				[
					'public'              => true,
					'exclude_from_search' => false,
				]
			);

			// Get posts types counts.
			$params  = new SqlParameters();
			$sql     = 'SELECT post_type, count(ID) AS post_count
				FROM ' . $params->identifier( $wpdb->posts ) . '
				WHERE post_status = ' . $params->string( 'publish' ) . '
					AND post_type IN ' . $params->string_list( $types ) . '
				GROUP BY post_type';
			$results = $wpdb->get_results(
				// phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
				$wpdb->prepare( $sql, $params->get_values() ),
				\ARRAY_A
			);

			$post_types = array_combine(
				array_column( $results, 'post_type' ),
				array_map( 'intval', array_column( $results, 'post_count' ) )
			);

			// Group results.
			$block_api   = new BlockUsageApi();
			$pattern_api = new PatternUsageApi();
			$report      = [
				'block_types'    => $block_api->get_count(),
				'block_patterns' => $pattern_api->get_count(),
				'post_types'     => $post_types,
			];

			return $report;
		}

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {
			$current_user = wp_get_current_user();

			if ( ( in_array( 'administrator', $current_user->roles, true ) || in_array( 'editor', $current_user->roles, true ) ) && current_user_can( 'edit_posts' ) ) {
				add_submenu_page(
					P4GBKS_PLUGIN_SLUG_NAME,
					__( 'Report', 'planet4-blocks-backend' ),
					__( 'Report', 'planet4-blocks-backend' ),
					'edit_posts',
					'plugin_blocks_report',
					[ $this, 'plugin_blocks_report' ]
				);

				add_submenu_page(
					P4GBKS_PLUGIN_SLUG_NAME,
					__( 'Pattern Report', 'planet4-blocks-backend' ),
					__( 'Pattern Report', 'planet4-blocks-backend' ),
					'edit_posts',
					'plugin_patterns_report',
					[ $this, 'plugin_patterns_report' ]
				);
			}
		}

		/**
		 * Block usage report page.
		 */
		public function plugin_blocks_report() {
			// Nonce verify.
			if ( isset( $_REQUEST['filter_action'] ) ) {
				check_admin_referer( 'bulk-' . BlockUsageTable::PLURAL );
			}

			// Create table.
			$args  = [
				'block_usage'    => new BlockUsage(),
				'block_registry' => WP_Block_Type_Registry::get_instance(),
			];
			$table = new BlockUsageTable( $args );

			// Prepare data.
			$special_filter = isset( $_REQUEST['unregistered'] ) ? 'unregistered'
				: ( isset( $_REQUEST['unallowed'] ) ? 'unallowed' : null );
			$table->prepare_items(
				BlockParameters::from_request( $_REQUEST ),
				$_REQUEST['group'] ?? null,
				$special_filter
			);

			// Display data.
			echo '<div class="wrap">
				<h1 class="wp-heading-inline">Block usage</h1>
				<hr class="wp-header-end">';

			echo '<form id="blocks-report" method="get">';
			$table->views();
			$table->search_box( 'Search in block attributes', 'blocks-report' );
			$table->display();
			echo '<input type="hidden" name="action"
				value="' . esc_attr( BlockUsageTable::ACTION_NAME ) . '"/>';
			echo '</form>';
			echo '</div>';
		}

		/**
		 * Pattern usage report page.
		 */
		public function plugin_patterns_report() {
			// Nonce verify.
			if ( isset( $_REQUEST['filter_action'] ) ) {
				check_admin_referer( 'bulk-' . PatternUsageTable::PLURAL );
			}

			// Create table.
			$args  = [
				'pattern_usage'    => new PatternUsage(),
				'pattern_registry' => WP_Block_Patterns_Registry::get_instance(),
			];
			$table = new PatternUsageTable( $args );

			// Prepare data.
			$table->prepare_items(
				PatternParameters::from_request( $_REQUEST ),
				$_REQUEST['group'] ?? null
			);

			// Display data.
			echo '<div class="wrap">
				<h1 class="wp-heading-inline">Pattern usage</h1>
				<hr class="wp-header-end">';

			echo '<form id="patterns-report" method="get">';
			$table->views();
			$table->display();
			echo '<input type="hidden" name="action"
				value="' . esc_attr( PatternUsageTable::ACTION_NAME ) . '"/>';
			echo '</form>';
			echo '</div>';
		}
	}
}
