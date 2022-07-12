<?php
/**
 * Get Informed pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Get Informed.
 *
 * @package P4GBKS\Patterns
 */
class GetInformed extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/get-informed-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => __( 'Get Informed', 'planet4-blocks-backend' ),
			'blockTypes' => [ 'core/post-content' ],
			'categories' => [ 'layouts' ],
			'content'    => '
				<!-- wp:group {"className":"block"} -->
					<div class="wp-block-group">
						' . QuickLinks::get_config( [ 'title_placeholder' => __( 'Explore by topic', 'planet4-blocks' ) ] )['content'] . '
						' . SideImageWithTextAndCta::get_config( [ 'title_placeholder' => __( 'Topic 1', 'planet4-blocks' ) ] )['content'] . '
						' . SideImageWithTextAndCta::get_config(
							[
								'media_position'    => 'right',
								'title_placeholder' => __( 'Topic 2', 'planet4-blocks' ),
							]
						)['content'] . '
						' . SideImageWithTextAndCta::get_config( [ 'title_placeholder' => __( 'Topic 3', 'planet4-blocks' ) ] )['content'] . '
						' . Issues::get_config( [ 'title_placeholder' => __( 'Issues we work on', 'planet4-blocks' ) ] )['content'] . '
						<!-- wp:planet4-blocks/articles {"article_heading":"' . __( 'Our recent victories', 'planet4-blocks' ) . '"} /-->
						<!-- wp:planet4-blocks/gallery {
							"className":"is-style-grid",
							"gallery_block_title":"' . __( 'Our latest actions around the world', 'planet4-blocks' ) . '"
						} /-->
						<!-- wp:planet4-blocks/articles {"article_heading":"' . __( 'Latest news & stories', 'planet4-blocks' ) . '"} /-->
						<!-- wp:group {"backgroundColor":"grey-05"} -->
							<div class="wp-block-group has-grey-05-background-color has-background">
								<!-- wp:gravityforms/form /-->
							</div>
						<!-- /wp:group -->
					</div>
				<!-- /wp:group -->
			',
		];
	}
}
