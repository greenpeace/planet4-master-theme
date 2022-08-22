<?php
/**
 * Highlighted CTA pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Highlighted CTA.
 *
 * @package P4GBKS\Patterns
 */
class HighlightedCta extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/highlighted-cta';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		$classname         = self::get_classname();
		$title_placeholder = $params['title_placeholder'] ?? '';

		return [
			'title'      => 'Highlighted CTA',
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:columns {"className":"block ' . $classname . '","textColor":"white","backgroundColor":"dark-blue"} -->
					<div class="wp-block-columns block ' . $classname . ' has-dark-blue-background-color has-text-color has-background has-white-color">
						<!-- wp:column -->
							<div class="wp-block-column">
								<!-- wp:image {"align":"center","className":"force-no-lightbox force-no-caption"} -->
									<div class="wp-block-image force-no-lightbox force-no-caption">
										<figure class="aligncenter">
											<img src="' . esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-80x80.jpg" alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"/>
										</figure>
									</div>
								<!-- /wp:image -->
								<!-- wp:heading {"placeholder":"' . __( 'Enter text', 'planet4-blocks-backend' ) . '","align":"center","className":"has-text-align-center","level":3} -->
									<h3 class="has-text-align-center">' . $title_placeholder . '</h3>
								<!-- /wp:heading -->
								<!-- wp:spacer {"height":"16px"} -->
									<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
								<!-- /wp:spacer -->
								<!-- wp:buttons {"layout":{"type":"flex","justifyContent":"center"}} -->
									<div class="wp-block-buttons">
										<!-- wp:button {"className":"is-style-transparent"} /-->
									</div>
								<!-- /wp:buttons -->
								<!-- wp:spacer {"height":"16px"} -->
									<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
								<!-- /wp:spacer -->
							</div>
						<!-- /wp:column -->
					</div>
				<!-- /wp:columns -->
			',
		];
	}
}
