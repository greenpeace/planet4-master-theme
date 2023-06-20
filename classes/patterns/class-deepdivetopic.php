<?php
/**
 * Deep Dive Topic pattern class.
 *
 * @package P4GBKS
 */

namespace P4GBKS\Patterns;

use P4GBKS\Patterns\Templates\Covers;

/**
 * Class Deep Dive Topic.
 *
 * @package P4GBKS\Patterns
 */
class DeepDiveTopic extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/deep-dive-topic-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		$classname = self::get_classname();

		return [
			'title'      => 'Deep Dive Topic',
			'categories' => [ 'layouts' ],
			'blockTypes' => [ 'core/post-content' ],
			'content'    => '
				<!-- wp:planet4-block-templates/deep-dive-topic '
					. wp_json_encode( $params, \JSON_FORCE_OBJECT )
					. ' /-->
			',
		];
	}
}
