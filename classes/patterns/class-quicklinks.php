<?php
/**
 * Quick Links pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Quick Links.
 *
 * @package P4GBKS\Patterns
 */
class QuickLinks extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/quick-links';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => 'Quick Links',
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:planet4-block-templates/quick-links ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
