<?php
/**
 * Homepage pattern layout class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

use P4GBKS\Patterns\Templates\CarouselHeader;
use P4GBKS\Patterns\Templates\Covers;
use P4GBKS\Patterns\Templates\GravityFormWithText;

/**
 * This class is used for returning a homepage pattern layout template.
 *
 * @package P4GBKS\Patterns
 */
class Homepage extends Block_Pattern {

	/**
	 * @inheritDoc
	 */
	public static function get_name(): string {
		return 'p4/homepage-pattern-layout';
	}

	/**
	 * @inheritDoc
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => __( 'Homepage pattern layout', 'planet4-blocks-backend' ),
			'blockTypes' => [ 'core/post-content' ],
			'categories' => [ 'layouts' ],
			'content'    => '
			<!-- wp:group -->
			<div class="wp-block-group">

				' . CarouselHeader::get_content() . '

				' . Issues::get_config( [ 'title_placeholder' => __( 'The issues we work on', 'planet4-blocks-backend' ) ] )['content'] . '

				<!-- wp:spacer {"height":"88px"} -->
				<div style="height:88px" aria-hidden="true" class="wp-block-spacer"></div>
				<!-- /wp:spacer -->

				<!-- wp:planet4-blocks/articles {
					"article_heading":"' . __( 'Read our Stories', 'planet4-blocks' ) . '"
				} /-->

				<!-- wp:spacer {"height":"56px"} -->
				<div style="height:56px" aria-hidden="true" class="wp-block-spacer"></div>
				<!-- /wp:spacer -->

				' . SideImageWithTextAndCta::get_config( [ 'title_placeholder' => __( 'Get to know us', 'planet4-blocks-backend' ) ] )['content'] . '

				<!-- wp:spacer {"height":"30px"} -->
				<div style="height:30px" aria-hidden="true" class="wp-block-spacer"></div>
				<!-- /wp:spacer -->

				' . SideImageWithTextAndCta::get_config(
					[
						'media_position'    => 'right',
						'title_placeholder' => __(
							'We win campaigns',
							'planet4-blocks-backend'
						),
					]
				)['content'] . '

				<!-- wp:spacer {"height":"56px"} -->
				<div style="height:56px" aria-hidden="true" class="wp-block-spacer"></div>
				<!-- /wp:spacer -->

				' . Covers::get_content() . '

				<!-- wp:spacer {"height":"72px"} -->
				<div style="height:72px" aria-hidden="true" class="wp-block-spacer"></div>
				<!-- /wp:spacer -->

				' . GravityFormWithText::get_content() . '

			</div>
			<!-- /wp:group -->
			',
		];
	}
}
