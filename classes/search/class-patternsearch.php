<?php
/**
 * Pattern search
 *
 * @package P4BKS\Search
 */

namespace P4GBKS\Search;

use P4GBKS\Search\Block\Sql\SqlQuery as BlockSqlQuery;
use P4GBKS\Search\Pattern\Query\Parameters;
use P4GBKS\Search\Block\Query\Parameters as BlockSearchParameters;
use P4GBKS\Search\Pattern\PatternData;
use P4\MasterTheme\SqlParameters;

/**
 * Search for posts containing specific patterns
 */
class PatternSearch {
	public const DEFAULT_POST_TYPE = [ 'post', 'page' ];

	public const DEFAULT_POST_STATUS = [ 'publish', 'private', 'draft', 'pending', 'future' ];

	/**
	 * @param Parameters $params Query parameters.
	 * @return int[] list of posts IDs.
	 */
	public function get_posts( Parameters $params ): array {
		$patterns = array_map(
			fn ( $pattern ) => PatternData::from_name( $pattern ),
			$params->name()
		);

		return array_unique(
			array_filter(
				array_merge(
					$this->query_by_pattern_classname( $params, ...$patterns ),
					$this->query_by_pattern_blocks( $params, ...$patterns )
				)
			)
		);
	}

	/**
	 * @param Parameters  $params          Search parameters.
	 * @param PatternData ...$pattern_data Pattern data.
	 * @return int[] List of posts IDs.
	 */
	private function query_by_pattern_blocks(
		Parameters $params,
		PatternData ...$pattern_data
	): array {
		// Query posts with all blocks of tree.
		$block_query = new BlockSqlQuery();

		$post_ids = [];
		foreach ( $pattern_data as $pattern ) {
			$block_params = array_map(
				fn ( $block_name ) => BlockSearchParameters::from_array(
					[
						'name'        => $block_name,
						'post_status' => $params->post_status() ?? self::DEFAULT_POST_STATUS,
						'post_type'   => $params->post_type() ?? self::DEFAULT_POST_TYPE,
					]
				),
				$pattern->block_list
			);

			$post_ids = array_merge( $post_ids, $block_query->get_posts( ...$block_params ) );
		}

		return array_unique( $post_ids );
	}

	/**
	 * Query posts by pattern classname.
	 *
	 * @param Parameters  $params          Search parameters.
	 * @param PatternData ...$pattern_data Pattern data.
	 * @return int[] List of posts IDs.
	 */
	private function query_by_pattern_classname(
		Parameters $params,
		PatternData ...$pattern_data
	): array {
		$classes = array_map(
			fn ( $p ) => $p->classname,
			$pattern_data
		);
		if ( empty( $classes ) ) {
			return [];
		}

		global $wpdb;

		$like = array_map( fn ( $c ) => "post_content LIKE '%$c%'", $classes );
		$like = implode( ' OR ', $like );

		$sql_params = new SqlParameters();
		$query      = 'SELECT ID
			FROM ' . $sql_params->identifier( $wpdb->posts ) . '
			WHERE post_status IN ' . $sql_params->string_list(
				$params->post_status() ?? self::DEFAULT_POST_STATUS
			) . '
			AND post_type IN ' . $sql_params->string_list(
				$params->post_type() ?? self::DEFAULT_POST_TYPE
			) . '
			AND ( ' . $like . ' )';

		$results = $wpdb->get_results(
			$wpdb->prepare( $query, $sql_params->get_values() ) // phpcs:ignore
		);

		return array_map(
			fn ( $r ) => (int) $r->ID,
			$results
		);
	}
}
