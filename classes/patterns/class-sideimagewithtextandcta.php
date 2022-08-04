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
		$media_link               = esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-546x415.jpg';
		$media_position           = $params['media_position'] ?? '';
		$media_position_class     = 'right' === $media_position ? 'has-media-on-the-right' : '';
		$media_position_attribute = 'right' === $media_position ? ',"mediaPosition":"right"' : '';
		$title_placeholder        = $params['title_placeholder'] ?? '';
		$background_color         = $params['background_color'] ?? null;
		$background_attribute     = $background_color ? ',"backgroundColor":"' . $background_color . '"' : '';
		$background_classes       = $background_color ? 'has-' . $background_color . '-background-color has-background' : '';
		$alignfull                = $params['alignfull'] ?? false;
		$alignment_class          = $alignfull ? 'alignfull' : '';
		$alignment_attribute      = $alignfull ? ',"align":"full"' : '';

		return [
			'title'      => __( 'Side image with text and CTA', 'planet4-blocks-backend' ),
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:media-text {"mediaLink":"' . $media_link . '","mediaType":"image","className":"block ' . $media_position_class . '"' . $background_attribute . $alignment_attribute . $media_position_attribute . '} -->
					<div class="wp-block-media-text ' . $alignment_class . ' is-stacked-on-mobile block ' . $media_position_class . ' ' . $background_classes . '">
						<figure class="wp-block-media-text__media">
							<img src="' . $media_link . '" alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"/>
						</figure>
						<div class="wp-block-media-text__content">
							<!-- wp:heading {"placeholder":"' . __( 'Enter title', 'planet4-blocks-backend' ) . '"} -->
								<h2>' . $title_placeholder . '</h2>
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
