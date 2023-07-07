<?php
/**
 * Action pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Action.
 *
 * @package P4GBKS\Patterns
 */
class Action extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/action-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => 'Action',
			'categories' => [ 'layouts' ],
			'blockTypes' => [ 'core/post-content' ],
			'postTypes'  => [ 'page', 'p4_action', 'campaign' ],
			'content'    => '
				<!-- wp:planet4-block-templates/action ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
