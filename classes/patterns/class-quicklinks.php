<?php
/**
 * Quick Links pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Quick Links.
 *
 * @package P4GBKS\Patterns
 */
class QuickLinks extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/quick-links';
	}

	/**
	 * Returns the template for one column.
	 */
	public static function get_column_template(): string {
		return '
			<!-- wp:column -->
				<div class="wp-block-column">
					<!-- wp:group {"className":"group-stretched-link"} -->
						<div class="wp-block-group group-stretched-link">
							<!-- wp:image {"align":"center","className":"is-style-rounded-90 force-no-lightbox force-no-caption mb-0"} -->
								<div class="wp-block-image is-style-rounded-90 force-no-lightbox force-no-caption mb-0">
									<figure class="aligncenter">
										<img
											src="' . esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-90x90.jpg"
											alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"
										/>
									</figure>
								</div>
							<!-- /wp:image -->
							<!-- wp:spacer {"height":"16px"} -->
								<div style="height:16px" aria-hidden="true" class="wp-block-spacer"></div>
							<!-- /wp:spacer -->
							<!-- wp:heading {
								"level":5,
								"style":{"typography":{"fontSize":"1rem"}},
								"align":"center",
								"className":"has-text-align-center",
								"placeholder":"' . __( 'Category', 'planet4-blocks-backend' ) . '"
							} -->
								<h5 style="font-size:1rem;" class="has-text-align-center"></h5>
							<!-- /wp:heading -->
						</div>
					<!-- /wp:group -->
				</div>
			<!-- /wp:column -->
		';
	}

	/**
	 * Returns the pattern config.
	 * We start with 6 columns, but editors can easily remove and/or duplicate them.
	 */
	public static function get_config(): array {
		return [
			'title'      => __( 'Quick Links', 'planet4-blocks-backend' ),
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:group {"className":"block","align":"full","backgroundColor":"grey-05"} -->
					<div class="wp-block-group alignfull block has-grey-05-background-color has-background">
						<!-- wp:group {"className":"container"} -->
							<div class="wp-block-group container">
								<!-- wp:spacer {"height":"24px"} -->
									<div style="height:24px" aria-hidden="true" class="wp-block-spacer"></div>
								<!-- /wp:spacer -->
								<!-- wp:heading {"level":4,"placeholder":"' . __( 'Enter title', 'planet4-blocks-backend' ) . '"} -->
									<h4></h4>
								<!-- /wp:heading -->
								<!-- wp:columns {"isStackedOnMobile":false,"className":"is-style-mobile-carousel"} -->
									<div class="wp-block-columns is-not-stacked-on-mobile is-style-mobile-carousel">
											' . self::get_column_template() . '
											' . self::get_column_template() . '
											' . self::get_column_template() . '
											' . self::get_column_template() . '
											' . self::get_column_template() . '
											' . self::get_column_template() . '
									</div>
								<!-- /wp:columns -->
							</div>
						<!-- /wp:group -->
					</div>
				<!-- /wp:group -->
			',
		];
	}
}
