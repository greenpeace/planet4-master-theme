<?php
/**
 * Side image with text and CTA pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Side image with text and CTA.
 *
 * @package P4GBKS\Patterns
 */
class SideImageWithTextAndCta extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/side-image-with-text-and-cta';
	}

	/**
	 * Returns the pattern config.
	 */
	public static function get_config(): array {
		return [
			'title'      => __( 'Side image with text and CTA', 'planet4-blocks-backend' ),
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:media-text {"className":"block"} -->
					<div class="wp-block-media-text alignwide is-stacked-on-mobile block">
						<figure class="wp-block-media-text__media"></figure>
						<div class="wp-block-media-text__content">
							<!-- wp:heading {"placeholder":"' . __( 'Enter title', 'planet4-blocks-backend' ) . '"} -->
								<h2></h2>
							<!-- /wp:heading -->
							<!-- wp:paragraph {"placeholder":"' . __( 'Enter description', 'planet4-blocks-backend' ) . '"} -->
								<p></p>
							<!-- /wp:paragraph -->
							<!-- wp:buttons -->
								<div class="wp-block-buttons">
									<!-- wp:button /-->
								</div>
							<!-- /wp:buttons -->
						</div>
					</div>
				<!-- /wp:media-text -->
			',
		];
	}
}
