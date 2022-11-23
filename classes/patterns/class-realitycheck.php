<?php
/**
 * Reality Check pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Reality Check.
 *
 * @package P4GBKS\Patterns
 */
class RealityCheck extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/reality-check';
	}


	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {

		return [
			'title'      => 'Reality Check',
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:planet4-block-templates/reality-check ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
