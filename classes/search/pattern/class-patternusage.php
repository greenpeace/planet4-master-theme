<?php
/**
 * Table displaying patterns usage
 *
 * @package P4BKS\Search
 */

namespace P4GBKS\Search\Pattern;

use WP_Block_Parser;
use WP_Block_Patterns_Registry;
use WP_Post;
use P4GBKS\Search\PatternSearch;
use P4GBKS\Search\Pattern\Query\Parameters;

/**
 * Prepare pattern usage, using native WordPress table
 */
class PatternUsage {
	/**
	 * @var PatternSearch
	 */
	private $search;

	/**
	 * @var WP_Block_Parser
	 */
	private $parser;

	/**
	 * @var WP_Block_Patterns_Registry
	 */
	private $registry;

	/**
	 * @param PatternSearch|null   $search Search class.
	 * @param WP_Block_Parser|null $parser Block parser.
	 */
	public function __construct(
		?PatternSearch $search = null,
		?WP_Block_Parser $parser = null
	) {
		$this->search   = $search ?? new PatternSearch();
		$this->parser   = $parser ?? new WP_Block_Parser();
		$this->registry = WP_Block_Patterns_Registry::get_instance();
	}

	/**
	 * @param Parameters $params Search parameters.
	 * @return array
	 */
	public function get_patterns( Parameters $params ): array {
		$posts_ids = $this->search->get_posts( $params );

		return $this->get_filtered_patterns( $posts_ids, $params );
	}

	/**
	 * @param int[]      $posts_ids Posts ids.
	 * @param Parameters $params    Search parameters.
	 * @return array
	 */
	private function get_filtered_patterns( array $posts_ids, Parameters $params ): array {
		$chunks = array_chunk( $posts_ids, 50 );

		$post_args = [
			'orderby'     => empty( $params->order() ) ? null : array_fill_keys( $params->order(), 'ASC' ),
			'post_status' => $params->post_status(),
			'post_type'   => $params->post_type(),
		];

		$patterns = [];
		foreach ( $chunks as $chunk ) {
			/** @var WP_Post[] $posts */
			$posts = get_posts( array_merge( [ 'include' => $chunk ], $post_args ) );

			foreach ( $posts as $post ) {
				$post_struct = new ContentStructure();
				$post_struct->parse_content( $post->post_content ?? '' );

				foreach ( $params->name() as $pattern_name ) {
					$pattern = PatternData::from_name( $pattern_name );

					// struct matches.
					$struct_occ = substr_count(
						$post_struct->get_content_signature(),
						$pattern->signature
					);
					if ( $struct_occ > 0 ) {
						$patterns[] = $this->format_pattern_data(
							$pattern,
							$post,
							$struct_occ,
							'structure'
						);
					}

					// class matches.
					$class_occ = round(
						substr_count( $post->post_content, $pattern->classname ) / 2
					);
					if ( $class_occ > 0 ) {
						$patterns[] = $this->format_pattern_data(
							$pattern,
							$post,
							$class_occ,
							'classname'
						);
					}
				}
			}
		}

		return $patterns;
	}

	/**
	 * @param PatternData $pattern     PatternData   Pattern.
	 * @param WP_Post     $post        WP_Post Post.
	 * @param int         $occurrences Pattern occurrences.
	 * @param string      $match_type  Match type.
	 */
	private function format_pattern_data(
		PatternData $pattern,
		WP_Post $post,
		int $occurrences,
		string $match_type
	): array {
		return [
			'post_title'    => $post->post_title,
			'pattern_name'  => $pattern->name,
			'pattern_title' => $pattern->title,
			'pattern_occ'   => $occurrences ?? 1,
			'post_date'     => $post->post_date,
			'post_modified' => $post->post_modified,
			'post_id'       => $post->ID,
			'post_status'   => $post->post_status,
			'match_type'    => $match_type,
		];
	}
}
