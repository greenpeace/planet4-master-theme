<?php
/**
 * BlankPage pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * This class is used for returning a blank page with a default content.
 *
 * @package P4GBKS\Patterns
 */
class BlankPage extends Block_Pattern {

	/**
	 * @inheritDoc
	 */
	public static function get_name(): string {
		return 'p4/blank-page-pattern-layout';
	}

	/**
	 * @inheritDoc
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => __( 'Blank page', 'planet4-blocks-backend' ),
			'blockTypes' => [ 'core/post-content' ],
			'categories' => [ 'layouts' ],
			'content'    => '
				<!-- wp:paragraph {"placeholder":"' . __( 'Enter text', 'planet4-blocks-backend' ) . '"} -->
				<p></p>
				<!-- /wp:paragraph -->
			',
		];
	}
}
