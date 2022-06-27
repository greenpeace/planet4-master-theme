<?php
/**
 * Blocks Usage class
 *
 * @package P4BKS\Controllers\Menu
 * @since 1.40.0
 */

namespace P4GBKS\Controllers\Menu;

use P4\MasterTheme\SqlParameters;
use P4\MasterTheme\Exception\SqlInIsEmpty;
use P4GBKS\Search\Block\BlockUsage;
use P4GBKS\Search\Block\BlockUsageTable;
use P4GBKS\Search\Block\BlockUsageApi;
use P4GBKS\Search\Block\Query\Parameters;
use WP_Block_Type_Registry;

if ( ! class_exists( 'Blocks_Usage_Controller' ) ) {

	/**
	 * Class Blocks_Usage_Controller
	 */
	class Blocks_Usage_Controller extends Controller {
		/**
		 * @var string[] Possible prefixes for planet4 blocks.
		 */
		private const PLANET4_PREFIXES = [
			'planet4',
			'p4',
		];

		/**
		 * String to use for Post with no title.
		 */
		private const NO_TITLE = '(no title)';


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
		}

		/**
		 * Register API route for report of blocks usage in pages/posts.
		 */
		public function plugin_blocks_report_register_rest_route() {
			register_rest_route(
				'plugin_blocks/v2',
				'/plugin_blocks_report/',
				[
					'methods'             => 'GET',
					'callback'            => [ $this, 'plugin_blocks_report_json' ],
					'permission_callback' => '__return_true',
				]
			);
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
		 * Finds blocks usage in pages/posts.
		 */
		public function plugin_blocks_report_json() {
			$cache_key = 'plugin-blocks-report';
			$report    = wp_cache_get( $cache_key );
			if ( ! $report ) {
				$report = $this->plugin_blocks_report( 'json' );
				wp_cache_add( $cache_key, $report, '', 3600 );
			}

			return $report;
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
			$api    = new BlockUsageApi();
			$report = [
				'block_types' => $api->get_count(),
				'post_types'  => $post_types,
			];

			return $report;
		}

		/**
		 * Create menu/submenu entry.
		 */
		public function create_admin_menu() {

			$current_user = wp_get_current_user();

			if ( in_array( 'administrator', $current_user->roles, true ) && current_user_can( 'manage_options' ) ) {
				add_submenu_page(
					P4GBKS_PLUGIN_SLUG_NAME,
					__( 'Usage', 'planet4-blocks-backend' ),
					__( 'Usage', 'planet4-blocks-backend' ),
					'manage_options',
					'plugin_blocks_report',
					[ $this, 'plugin_blocks_report' ]
				);

				// Experimental block usage page, hidden from menu.
				add_submenu_page(
					P4GBKS_PLUGIN_SLUG_NAME,
					__( 'Report (Beta)', 'planet4-blocks-backend' ),
					__( 'Report (Beta)', 'planet4-blocks-backend' ),
					'manage_options',
					'plugin_blocks_report_beta',
					[ $this, 'plugin_blocks_report_beta' ]
				);
			}
		}

		/**
		 * Beta block usage report page.
		 *
		 * @todo before replacing current one:
		 * - review json report / keep or replace with new search
		 */
		public function plugin_blocks_report_beta() {
			// Nonce verify.
			if ( isset( $_REQUEST['filter_action'] ) ) {
				check_admin_referer( 'bulk-blocks' );
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
				Parameters::from_request( $_REQUEST ),
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
		}

		/**
		 * Finds blocks usage in pages/posts.
		 *
		 * @param String $type The Block report type.
		 *
		 * @throws SqlInIsEmpty Should not happen in practice as everyone has types with blocks.
		 */
		public function plugin_blocks_report( $type = 'text' ) {
			global $wpdb;
			$wpdb_prefix = $wpdb->prefix;

			$block_types = $this->get_block_types();
			$report      = [];

			if ( '' === $type ) {
				$type = 'text';
			}

			$types_with_blocks = self::get_post_types_with_blocks();

			// phpcs:disable
			foreach ( $block_types as $block_type ) {
				$block_comment = '%<!-- wp:' . $wpdb->esc_like( $block_type ) . ' %';

				$params = new SqlParameters();

				$sql = "SELECT ID, post_title
					FROM " . $params->identifier( $wpdb->posts ) . "
					WHERE post_status = 'publish'
					AND post_type IN " . $params->string_list( $types_with_blocks ) . "
					AND `post_content` LIKE " . $params->string( $block_comment ) . "
					ORDER BY post_title";

				$results = $wpdb->get_results(
					$wpdb->prepare( $sql, $params->get_values() )
				);

				if ( !$results ) { continue; }

				// Confusion between old and new covers.
				if ( 'planet4-blocks/covers' === $block_type ) {
					$block_type = 'Take Action Covers - Old block';
				}

				if ( 'text' === $type ) {
					echo '<hr>';
					echo '<h2>' . ucfirst( str_replace( '_', ' ', $block_type ) ) . '</h2>';
					echo '<table>';
					echo '<tr style="text-align: left">
							<th>' . __( 'ID', 'planet4-blocks-backend' ) . '</th>
							<th>' . __( 'Title', 'planet4-blocks-backend' ) . '</th>
					</tr>';
					foreach ( $results as $result ) {
						$title = empty($result->post_title) ? self::NO_TITLE : $result->post_title;
						echo  '<tr><td><a href="post.php?post=' . $result->ID . '&action=edit" >' . $result->ID . '</a></td>';
						echo  '<td><a href="' . get_permalink( $result->ID ) . '" target="_blank">' . $title . '</a></td></tr>';
					}
					echo '</table>';
				} else {
					$report[ ucfirst( str_replace( '_', ' ', $block_type ) ) ] = count($results);
				}
			}

			// Add to the report a breakdown of which tags are using a redirect page and which do not
			// The first query shows the ones that do not use a redirect page
			$sql = '( SELECT term.name, tt.term_id
							FROM %1$sterm_taxonomy AS tt,
								 %2$sterms AS term,
								 %3$stermmeta AS tm
							WHERE tt.`taxonomy`= \'post_tag\'
							AND term.term_id = tt.term_id
							AND tm.term_id=tt.term_id
							AND tm.meta_key=\'redirect_page\'
							AND tm.meta_value =\'\' )
						UNION
						( SELECT term.name, tt.term_id
							FROM %4$sterm_taxonomy AS tt,
								 %5$sterms AS term,
								 %6$stermmeta AS tm
							WHERE tt.`taxonomy`=\'post_tag\'
							AND term.term_id = tt.term_id
							AND tm.term_id=tt.term_id
							AND tm.term_id NOT IN (SELECT tt.term_id
													FROM %7$sterm_taxonomy AS tt,
														 %8$sterms AS term,
														 %9$stermmeta AS tm
													WHERE tt.`taxonomy`=\'post_tag\'
													AND term.term_id = tt.term_id
													AND tm.term_id=tt.term_id
													AND tm.meta_key=\'redirect_page\')
							GROUP BY term.name, tt.term_id )';
			$prepared_sql = $wpdb->prepare(
				$sql,
				[
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix
				]
			);
			$results = $wpdb->get_results( $prepared_sql );
			if ( 'text' === $type ) {
				echo '<hr>';
				echo '<h2>Tags without redirection page</h2>';
				echo '<table><tr style="text-align: left">
					<th>' . __( 'ID', 'planet4-blocks-backend' ) . '</th>
					<th>' . __( 'Title', 'planet4-blocks-backend' ) . '</th>
				</tr>';
				foreach ( $results as $result ) {
					$title = empty($result->name) ? self::NO_TITLE : $result->name;
					echo  '<tr><td><a href="term.php?taxonomy=post_tag&tag_ID=' . $result->term_id . '" >' . $result->term_id . '</a></td>';
					echo  '<td><a href="' . get_term_link( (int) $result->term_id ) . '" target="_blank">' . $title . '</a></td></tr>';
				}
				echo '</table>';
			} else {
				$report[ 'TagsNotUsingRedirectionPage' ] = count($results);
			}

			// Add to the report a breakdown of which tags are using a redirect page and which do not
			// The second query shows the ones that do use a redirect page
			$sql     = 'SELECT term.name, tm.meta_value, tt.term_id
						FROM %1$sterm_taxonomy AS tt,
							 %2$sterms AS term,
							 %3$stermmeta AS tm
						WHERE tt.`taxonomy`=\'post_tag\'
						AND term.term_id = tt.term_id
						AND tm.term_id=tt.term_id
						AND tm.meta_key=\'redirect_page\'
						AND tm.meta_value !=\'\'';
			$prepared_sql = $wpdb->prepare(
				$sql,
				[
					$wpdb_prefix,
					$wpdb_prefix,
					$wpdb_prefix,
				]
			);
			$results = $wpdb->get_results( $prepared_sql );
			if ( 'text' === $type ) {
				echo '<hr>';
				echo '<h2>Tags that use a redirection page</h2>';
				echo '<table><tr style="text-align: left">
					<th>' . __( 'ID', 'planet4-blocks-backend' ) . '</th>
					<th>' . __( 'Title', 'planet4-blocks-backend' ) . '</th>
				</tr>';
				foreach ( $results as $result ) {
					$title = empty($result->name) ? self::NO_TITLE : $result->name;
					echo  '<tr><td><a href="term.php?taxonomy=post_tag&tag_ID=' . $result->term_id . '" >' . $result->term_id . '</a></td>';
					echo  '<td><a href="' . get_term_link( (int) $result->term_id ) . '" target="_blank">' . $title . '</a></td></tr>';
				}
				echo '</table>';
			} else {
				$report[ 'TagsUsingRedirectionPage' ] = count($results);
			}

			// Add to the report a breakdown of Campaigns, pages & Posts count.
			$p4_page_types = [
				'campaign',
				'post',
				'page',
			];

			// SQL Query placeholders.
			$placeholders   = [];
			$pagetype_count = count( $p4_page_types );
			for ( $i = 2; $i < $pagetype_count + 2; $i++ ) {
				$placeholders[] = "'%$i\$s'";
			}
			$placeholders = implode( ',', $placeholders );

			$sql = 'SELECT post_type, count(ID) AS post_count
					FROM %1$s
					WHERE post_status = \'publish\'
						GROUP BY `post_type` HAVING `post_type` IN (' . $placeholders . ')';

			$values       = [];
			$values[0]    = $wpdb->posts;
			$values       = array_merge( $values, $p4_page_types );
			$prepared_sql = $wpdb->prepare( $sql, $values );
			$results      = $wpdb->get_results( $prepared_sql );

			if ( 'text' === $type ) {
				echo '<hr>';
				echo '<table><tr style="text-align: left">
					<th>' . __( 'Page type', 'planet4-blocks-backend' ) . '</th>
					<th>' . __( 'Count', 'planet4-blocks-backend' ) . '</th>
			</tr>';
				foreach ( $results as $result ) {
					echo '<tr><td>N of ' . ucfirst( $result->post_type ) . ' content type</td>';
					echo '<td><a href="edit.php?post_status=publish&post_type=' . $result->post_type . '" >' . $result->post_count . '</a></td></tr>';
				}
				echo '</table>';
			} else {
				foreach ( $results as $result ) {
					$report[ 'N-of-' . $result->post_type . '-content-type' ] = (int) $result->post_count;
				}
			}
			// phpcs:enable

			if ( 'json' === $type ) {
				return $report;
			}
		}

		/**
		 * Get all registered post types that "support blocks". This actually is not explicitly defined by itself, but
		 * depends on 2 things: type is registered with `show_in_rest => true` and the type supports `editor`. If both
		 * conditions are met the block editor is shown. If something weird and custom is done so that a post type does
		 * have blocks without these conditions being true then the blocks will not be picked up by the report.
		 *
		 * @return array All posts types that support blocks.
		 */
		private static function get_post_types_with_blocks(): array {
			$supports_editor = static function ( $type ) {
				return post_type_supports( $type, 'editor' );
			};

			return array_filter( get_post_types( [ 'show_in_rest' => true ] ), $supports_editor );
		}

		/**
		 * Return P4 and allowed core WP block types.
		 *
		 * @return string[]
		 */
		private function get_block_types(): array {
			$registered_block_types = WP_Block_Type_Registry::get_instance()->get_all_registered();

			$p4_block_types = array_filter(
				$registered_block_types,
				static function ( \WP_Block_Type $block_type ) {
					// even though the blocks in this repo all use namespace "planet4-blocks", NRO developed blocks
					// can have a different namespace. They do all start with "planet4-".
					foreach ( self::PLANET4_PREFIXES as $prefix ) {
						if ( strpos( $block_type->name, $prefix ) === 0 ) {
							return true;
						}
					}
					return false;
				}
			);
			// we only need the name.
			$p4_block_types = array_map(
				static function ( \WP_Block_Type $block_type ) {
					return $block_type->name;
				},
				$p4_block_types
			);

			$core_block_types = [
				'button',
				'columns',
				'group',
				'html',
				'media-text',
				'query',
				'query-pagination',
				'separator',
				'shortcode',
				'spacer',
				'table',
			];

			return array_merge( $p4_block_types, $core_block_types );
		}
	}
}
