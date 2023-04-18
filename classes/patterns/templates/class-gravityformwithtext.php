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
 * This class is used to return a Gravity Form block with text on the side.
 *
 * @package P4GBKS\Patterns\Templates
 */
class GravityFormWithText extends TemplatePattern {

	/**
	 * @inheritDoc
	 *
	 * @param array $params Optional array of parameters for the content.
	 */
	public static function get_content( $params = [] ): string {
		$is_new_identity  = get_theme_mod( 'new_identity_styles' );
		$background_color = $params['backgroundColor'] ?? null;
		if ( ! $background_color ) {
			$background_color = $is_new_identity ? 'beige-100' : 'grey-05';
		}

		return '<!-- wp:group {"align":"full","backgroundColor":"' . $background_color . '","style":{"spacing":{"padding":{"top":"80px","bottom":"50px"}}}} -->
			<div class="wp-block-group alignfull has-' . $background_color . '-background-color has-background" style="padding-top:80px;padding-bottom:50px;">

				<!-- wp:group {"className":"container"} -->
				<div class="wp-block-group container">

				<!-- wp:columns -->
				<div class="wp-block-columns">

					<!-- wp:column -->
					<div class="wp-block-column">

						<!-- wp:heading {"style":{"typography":{"fontSize":"40px"}},"placeholder":"' . __( 'Enter title', 'planet4-blocks-backend' ) . '"} -->
						<h2 style="font-size:40px;"></h2>
						<!-- /wp:heading -->

						<!-- wp:paragraph {"placeholder":"' . __( 'Enter description', 'planet4-blocks-backend' ) . '"} -->
						<p></p>
						<!-- /wp:paragraph -->

					</div>
					<!-- /wp:column -->

					<!-- wp:column -->
					<div class="wp-block-column">
						<!-- wp:gravityforms/form {"title":false,"description":false} /-->
					</div>
					<!-- /wp:column -->

				</div>
				<!-- /wp:columns -->

				</div>
				<!-- /wp:group -->
			</div>
			<!-- /wp:group -->
		';
	}
}
