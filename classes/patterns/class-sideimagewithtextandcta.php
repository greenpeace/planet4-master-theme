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
			'keywords'   => [ 'cta', 'side', 'image' ],
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:media-text {"className":"block"} -->
					<div class="wp-block-media-text alignwide is-stacked-on-mobile block">
						<figure class="wp-block-media-text__media"></figure>
						<div class="wp-block-media-text__content">
							<!-- wp:heading {"placeholder":"Enter title"} -->
								<h2></h2>
							<!-- /wp:heading -->
							<!-- wp:paragraph {"placeholder":"Enter description"} -->
								<p></p>
							<!-- /wp:paragraph -->
							<!-- wp:buttons -->
								<div class="wp-block-buttons">
									<!-- wp:button {"placeholder":"Enter CTA text"} /-->
								</div>
							<!-- /wp:buttons -->
						</div>
					</div>
				<!-- /wp:media-text -->
			',
		];
	}
}
