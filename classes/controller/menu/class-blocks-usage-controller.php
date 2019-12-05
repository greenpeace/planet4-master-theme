<?php
/**
 * Blocks Usage class
 *
 * @package P4BKS\Controllers\Menu
 * @since 1.40.0
 */

namespace P4GBKS\Controllers\Menu;

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
		}

		/**
		 * Register API route for report of blocks usage in pages/posts.
		 */
		public function plugin_blocks_report_register_rest_route() {
			register_rest_route(
				'plugin_blocks/v2',
				'/plugin_blocks_report/',
				[
					'methods'  => 'GET',
					'callback' => [ $this, 'plugin_blocks_report_json' ],
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
			}
		}

		/**
		 * Finds blocks usage in pages/posts.
		 *
		 * @param String $type The Block report type.
		 */
		public function plugin_blocks_report( $type = 'text' ) {
			global $wpdb;
			$wpdb_prefix = $wpdb->prefix;

			$block_types = $this->get_block_types();
			$report      = [];

			if ( '' === $type ) {
				$type = 'text';
			}

			// phpcs:disable
			foreach ( $block_types as $block_type ) {
				$block_comment = '%<!-- wp:' . $wpdb->esc_like( $block_type ) . ' %';

				$sql = $wpdb->prepare(
					"SELECT ID, post_title
					FROM `wp_posts` 
					WHERE post_status = 'publish' 
					AND `post_content` LIKE %s
					ORDER BY post_title",
					$block_comment );

				$results = $wpdb->get_results( $sql );

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
						echo '<tr><td>'.$result->ID .'</td>';
						echo  '<td><a href="post.php?post=' . $result->ID . '&action=edit" >' . $result->post_title . '</a></td></tr>';
					}
					echo '</table>';
				} else {
					$report[ ucfirst( str_replace( '_', ' ', $block_type ) ) ] = count($results);
				}
			}

			// Add to the report a breakdown of different styles for carousel Header
			$sql = 'SELECT ID, post_title
                    FROM %1$s
                    WHERE post_status = \'publish\'
                        AND `post_content` REGEXP \'<!-- wp:planet4-blocks/carousel-header\'';

			$prepared_sql = $wpdb->prepare( $sql, $wpdb->posts );
			$results      = $wpdb->get_results( $prepared_sql );
			if ( 'text' === $type ) {
				echo '<hr>';
				echo '<h2>Carousel Header Full Width Classic style</h2>';
				echo '<table><tr style="text-align: left">
						<th>' . __( 'ID', 'planet4-blocks-backend' ) . '</th>
						<th>' . __( 'Title', 'planet4-blocks-backend' ) . '</th>
				</tr>';
				foreach ($results as $result) {
					echo '<tr><td>' . $result->ID . '</td>';
					echo '<td><a href="post.php?post=' . $result->ID . '&action=edit" >' . $result->post_title . '</a></td></tr>';
				}
				echo '</table>';
			} else {
				$report[ ucfirst( 'planet4-blocks/carousel-header-Full-Width-Classic' ) ] = count($results);
			}

			// Add to the report a breakdown of different styles for carousel Header
			// Given that the default (if no style is defined) is the Slide to Gray, include in the query
			// everything that is not Full Width Classic.
			$sql = 'SELECT ID, post_title
                    FROM %1$s
                    WHERE post_status = \'publish\'
                        AND `post_content` REGEXP \'<!-- wp:planet4-blocks/carousel-header\'
                        AND ID NOT IN (SELECT ID
                            FROM %2$s
                            WHERE post_status = \'publish\'
                            AND `post_content` REGEXP \'<!-- wp:planet4-blocks/carousel-header\')';

			$prepared_sql = $wpdb->prepare( $sql, [ $wpdb->posts, $wpdb->posts ] );
			$results      = $wpdb->get_results( $prepared_sql );
			if ( 'text' === $type ) {
				echo '<hr>';
				echo '<h2>Carousel Header Zoom and Slide to Grey</h2>';
				echo '<table><tr style="text-align: left">
						<th>' . __( 'ID', 'planet4-blocks-backend' ) . '</th>
						<th>' . __( 'Title', 'planet4-blocks-backend' ) . '</th>
				</tr>';
				foreach ( $results as $result ) {
					echo '<tr><td>'.$result->ID .'</td>';
					echo  '<td><a href="post.php?post=' . $result->ID . '&action=edit" >' . $result->post_title . '</a></td></tr>';
				}
				echo '</table>';
			} else {
				$report[ ucfirst( 'planet4-blocks/carousel-header-Zoom-And-Slide' ) ] = count($results);
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
					echo '<tr><td>'.$result->term_id .'</td>';
					echo '<td><a href="term.php?taxonomy=post_tag&tag_ID=' . $result->term_id . '" >' . $result->name . '</a></td></tr>';
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
					echo '<tr><td>'.$result->term_id .'</td>';
					echo '<td><a href="term.php?taxonomy=post_tag&tag_ID=' . $result->term_id . '" >' . $result->name . '</a></td></tr>';
				}
				echo '</table>';
			} else {
				$report[ 'TagsUsingRedirectionPage' ] = count($results);
			}
			// phpcs:enable

			if ( 'json' === $type ) {
				return $report;
			}
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
				'html',
				'table',
				'button',
				'separator',
				'spacer',
				'shortcode',
			];

			return array_merge( $p4_block_types, $core_block_types );
		}
	}
}
