<?php
/**
 * Reality Check pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Reality Check.
 *
 * @package P4GBKS\Patterns
 */
class RealityCheck extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/reality-check';
	}

	/**
	 * Returns the template for one column.
	 */
	public static function get_column_template(): string {
		return '
			<!-- wp:column -->
				<div class="wp-block-column">
					<!-- wp:image {"align":"center","className":"mb-0 force-no-lightbox"} -->
						<div class="wp-block-image mb-0 force-no-lightbox">
							<figure class="aligncenter">
								<img src="' . esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-75x75.jpg" alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"/>
							</figure>
						</div>
					<!-- /wp:image -->
					<!-- wp:heading {"style":{"typography":{"fontSize":"4rem"}},"align":"center","className":"has-text-align-center mb-0","placeholder":"' . __( 'Enter title', 'planet4-blocks-backend' ) . '"} -->
						<h2 style="font-size:4rem;" class="has-text-align-center mb-0"></h2>
					<!-- /wp:heading -->
					<!-- wp:paragraph {"align":"center","className":"has-text-align-center","placeholder":"' . __( 'Enter description', 'planet4-blocks-backend' ) . '"} -->
						<p class="has-text-align-center"></p>
					<!-- /wp:paragraph -->
					<!-- wp:spacer {"height":"16px"} -->
						<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
					<!-- /wp:spacer -->
				</div>
			<!-- /wp:column -->
		';
	}

	/**
	 * Returns the pattern config.
	 * We start with 3 columns, but editors can easily remove and/or duplicate them.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		return [
			'title'      => __( 'Reality Check', 'planet4-blocks-backend' ),
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:columns {"className":"block"} -->
					<div class="wp-block-columns block">
						' . self::get_column_template() . '
						' . self::get_column_template() . '
						' . self::get_column_template() . '
					</div>
				<!-- /wp:columns -->
			',
		];
	}
}
