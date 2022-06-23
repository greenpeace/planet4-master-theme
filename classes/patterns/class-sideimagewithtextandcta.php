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
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		$media_link = esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-546x415.jpg';
		return [
			'title'      => __( 'Side image with text and CTA', 'planet4-blocks-backend' ),
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:media-text {"mediaLink":"' . $media_link . '","mediaType":"image","className":"block"} -->
					<div class="wp-block-media-text alignwide is-stacked-on-mobile block">
						<figure class="wp-block-media-text__media">
							<img src="' . $media_link . '" alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"/>
						</figure>
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
