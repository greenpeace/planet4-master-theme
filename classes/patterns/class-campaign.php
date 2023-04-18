<?php
/**
 * Campaign pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

use P4GBKS\Patterns\Templates\Covers;
use P4GBKS\Patterns\Templates\GravityFormWithText;

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
		$classname       = self::get_classname();
		$is_new_identity = get_theme_mod( 'new_identity_styles' );

		return [
			'title'      => 'Campaign',
			'blockTypes' => [ 'core/post-content' ],
			'categories' => [ 'layouts' ],
			'content'    => '
				<!-- wp:group {"className":"block ' . $classname . '"} -->
					<div class="wp-block-group block ' . $classname . '">
						' . PageHeader::get_config( [ 'title_placeholder' => __( 'Page header title', 'planet4-blocks' ) ] )['content'] . '
						<!-- wp:spacer {"height":"64px"} -->
							<div style="height:64px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						' . SideImageWithTextAndCta::get_config(
							[ 'title' => __( 'The problem', 'planet4-blocks' ) ]
						)['content'] . '
						<!-- wp:spacer {"height":"32px"} -->
							<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						' . SideImageWithTextAndCta::get_config(
							[
								'title'         => __( 'The solution', 'planet4-blocks' ),
								'mediaPosition' => 'right',
							]
						)['content'] . '
						<!-- wp:spacer {"height":"32px"} -->
							<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						' . Covers::get_content( [ 'title_placeholder' => __( 'How you can help', 'planet4-blocks' ) ] ) . '
						<!-- wp:spacer {"height":"48px"} -->
							<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						' . GravityFormWithText::get_content() . '
					</div>
				<!-- /wp:group -->
			',
		];
	}
}
