<?php
/**
 * High-Level Topic pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class High-Level Topic.
 *
 * @package P4GBKS\Patterns
 */
class HighLevelTopic extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/high-level-topic-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 * We start with 3 columns, but editors can easily remove and/or duplicate them.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => 'High-Level Topic',
			'categories' => [ 'layouts' ],
			'blockTypes' => [ 'core/post-content' ],
			'content'    => '
				<!-- wp:planet4-block-templates/high-level-topic ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
