<?php
/**
 * Gravity Form reusable pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns\Templates;

use P4GBKS\Patterns\Templates\TemplatePattern;

/**
 * This class is used for returning a Gravity Form block with an image on the side.
 *
 * @package P4GBKS\Patterns\Templates
 */
class GravityFormWithImage extends TemplatePattern {

	/**
	 * @inheritDoc
	 *
	 * @param array $params Optional array of parameters for the content.
	 */
	public static function get_content( $params = [] ): string {
		$media_link           = esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-546x415.jpg';
		$background_color     = $params['background_color'] ?? null;
		$background_attribute = $background_color ? ',"backgroundColor":"' . $background_color . '"' : '';
		$background_classes   = $background_color ? 'has-' . $background_color . '-background-color has-background' : '';

		return '
			<!-- wp:media-text {"mediaLink":"' . $media_link . '","mediaType":"image","align":"full"' . $background_attribute . '} -->
				<div class="wp-block-media-text is-stacked-on-mobile alignfull ' . $background_classes . '">
					<figure class="wp-block-media-text__media">
						<img src="' . $media_link . '" alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"/>
					</figure>
					<div class="wp-block-media-text__content">
						<!-- wp:gravityforms/form /-->
					</div>
				</div>
			<!-- /wp:media-text -->
		';
	}
}
