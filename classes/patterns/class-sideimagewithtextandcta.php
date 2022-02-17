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
class SideImageWithTextAndCta extends Base_Pattern {

	/**
	 * Pattern name.
	 *
	 * @const string PATTERN_NAME.
	 */
	const PATTERN_NAME = 'side-image-with-text-and-cta';

	/**
	 * Side image with text and CTA pattern constructor.
	 */
	public function __construct() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		register_block_pattern(
			self::get_full_pattern_name(),
			[
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
			]
		);
	}
}
