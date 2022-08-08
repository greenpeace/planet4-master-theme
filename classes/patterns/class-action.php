<?php
/**
 * Action pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

use P4GBKS\Patterns\Templates\Covers;

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
		$media_link = esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-546x415.jpg';

		return [
			'title'      => __( 'Action', 'planet4-blocks-backend' ),
			'categories' => [ 'layouts' ],
			'content'    => '
				<!-- wp:group {"className":"block"} -->
					<div class="wp-block-group">
						<!-- wp:media-text {"mediaLink":"' . $media_link . '","mediaType":"image"} -->
							<div class="wp-block-media-text is-stacked-on-mobile">
								<figure class="wp-block-media-text__media">
									<img src="' . $media_link . '" alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"/>
								</figure>
								<div class="wp-block-media-text__content">
									<!-- wp:gravityforms/form /-->
								</div>
							</div>
						<!-- /wp:media-text -->
						<!-- wp:group {"backgroundColor":"grey-05","align":"full","style":{"spacing":{"padding":{"top":"80px","bottom":"80px"}}}} -->
							<div class="wp-block-group alignfull has-grey-05-background-color has-background" style="padding-top:80px;padding-bottom:80px;">
								<!-- wp:group {"className":"container"} -->
									<div class="wp-block-group container">
										' . SideImageWithTextAndCta::get_config(
										[
											'media_position'    => 'right',
											'title_placeholder' => __( 'The problem', 'planet4-blocks' ),
										]
									)['content'] . '
									</div>
								<!-- /wp:group -->
							</div>
						<!-- /wp:group -->
						' . Covers::get_content( [ 'cover_type' => 'take-action' ] ) . '
						<!-- wp:separator {"backgroundColor":"grey-20","className":"has-text-color has-grey-20-color has-grey-20-background-color has-background is-style-wide"} -->
						<hr class="wp-block-separator has-text-color has-grey-20-color has-alpha-channel-opacity has-grey-20-background-color has-background is-style-wide"/>
						<!-- /wp:separator -->
						' . QuickLinks::get_config(
							[
								'background_color'  => 'white',
								'title_placeholder' => __( 'Explore by topics', 'planet4-blocks' ),
							]
						)['content'] . '
					</div>
					<!-- wp:planet4-blocks/action-page-dummy /-->
				<!-- /wp:group -->
			',
		];
	}
}
