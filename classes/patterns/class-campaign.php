<?php
/**
 * Campaign pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Campaign.
 *
 * @package P4GBKS\Patterns
 */
class Campaign extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/campaign-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => 'Campaign',
			'blockTypes' => [ 'core/post-content' ],
			'categories' => [ 'layouts' ],
			'content'    => '
				<!-- wp:planet4-block-templates/campaign ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
