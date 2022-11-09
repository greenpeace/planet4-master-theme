<?php
/**
 * Deep Dive pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Deep Dive.
 *
 * @package P4GBKS\Patterns
 */
class DeepDive extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/deep-dive';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => 'Deep Dive',
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:planet4-block-templates/deep-dive ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
