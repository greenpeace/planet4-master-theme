<?php
/**
 * Action pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

use P4GBKS\Patterns\Templates\Covers;
use P4GBKS\Patterns\Templates\GravityFormWithImage;

/**
 * Class Action.
 *
 * @package P4GBKS\Patterns
 */
class Action extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/action-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		$classname = self::get_classname();

		return [
			'title'      => 'Action',
			'categories' => [ 'layouts' ],
			'blockTypes' => [ 'core/post-content' ],
			'postTypes'  => [ 'page', 'p4_action', 'campaign' ],
			'content'    => '
				<!-- wp:group {"className":"block ' . $classname . '"} -->
					<div class="wp-block-group block ' . $classname . '">
						' . GravityFormWithImage::get_content() . '
						<!-- wp:group {"backgroundColor":"grey-05","align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px"}}}} -->
							<div class="wp-block-group alignfull has-grey-05-background-color has-background" style="padding-top:80px;padding-bottom:80px;">
								<!-- wp:group {"className":"container"} -->
									<div class="wp-block-group container">
						' . SideImageWithTextAndCta::get_config(
							[
								'title'         => __( 'The problem', 'planet4-blocks' ),
								'mediaPosition' => 'right',
							]
						)['content'] . '
									</div>
								<!-- /wp:group -->
							</div>
						<!-- /wp:group -->
						' . Covers::get_content() . '
						<!-- wp:separator {"backgroundColor":"grey-20","className":"has-text-color has-grey-20-color has-grey-20-background-color has-background is-style-wide"} -->
						<hr class="wp-block-separator has-text-color has-grey-20-color has-alpha-channel-opacity has-grey-20-background-color has-background is-style-wide"/>
						<!-- /wp:separator -->
						' . QuickLinks::get_config(
							[
								'background_color' => 'white',
								'title'            => __( 'Explore by topics', 'planet4-blocks' ),
							]
						)['content'] . '
					</div>
				<!-- /wp:group -->
			',
		];
	}
}
