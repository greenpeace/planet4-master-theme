<?php
/**
 * TakeAction pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class TakeAction.
 *
 * @package P4GBKS\Patterns
 */
class TakeAction extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/take-action-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => 'Take Action',
			'categories' => [ 'layouts' ],
			'blockTypes' => [ 'core/post-content' ],
			'content'    => '
				<!-- wp:planet4-block-templates/take-action ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
