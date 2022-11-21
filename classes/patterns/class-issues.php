<?php
/**
 * Issues class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Issues pattern includes:
 * Column, Image, Heading, Paragraph, Media & Text.
 *
 * @package P4GBKS\Patterns
 */
class Issues extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/issues';
	}

	/**
	 * Returns the template for one media-text.
	 */
	public static function get_media_text_template(): string {
		$media_link = esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-40x40.jpg';

		return '<!-- wp:group {"className":"d-flex has-white-background-color has-background","style":{"border":{"radius":"4px"},"spacing":{"padding":{"top":"32px","right":"16px","bottom":"32px","left":"16px"}}},"backgroundColor":"white"} -->
			<div class="d-flex wp-block-group has-white-background-color has-background" style="border-radius:4px;padding-top:32px;padding-right:16px;padding-bottom:32px;padding-left:16px">
			<!-- wp:media-text {"mediaLink":"' . $media_link . '","mediaType":"image","mediaWidth":14,"mediaSizeSlug":"thumbnail","isStackedOnMobile":false,"imageFill":false,"className":"w-100 force-no-lightbox"} -->
			<div class="wp-block-media-text w-100 force-no-lightbox" style="grid-template-columns:14% auto">
			<figure class="wp-block-media-text__media">
				<img src="' . $media_link . '" alt="' . __( 'Default image', 'planet4-blocks-backend' ) . '"/>
			</figure>
			<div class="wp-block-media-text__content">
			<!-- wp:heading {"level":4,"className":"mb-0","placeholder":"' . __( 'Enter text', 'planet4-blocks-backend' ) . '","style":{"typography":{"fontSize":"1rem"}}} -->
			<h4 class="mb-0" style="font-size:1rem"></h4>
			<!-- /wp:heading --></div></div>
			<!-- /wp:media-text --></div>
		<!-- /wp:group -->';
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
			'title'      => 'Issues',
			'categories' => [ 'planet4' ],
			'content'    => '
				<!-- wp:group {"className":"' . $classname . '","align":"full","backgroundColor":"grey-05","style":{"spacing":{"padding":{"top":"80px","bottom":"80px"}}}} -->
				<div class="wp-block-group ' . $classname . ' alignfull has-grey-05-background-color has-background" style="padding-top:80px;padding-bottom:80px;">

					<!-- wp:group {"className":"container"} -->
					<div class="wp-block-group container">

						<!-- wp:heading {"textAlign":"center","level":2, "placeholder":"' . __( 'Enter title', 'planet4-blocks-backend' ) . '","style":{"spacing":{"margin":{"bottom":"24px"}}}} -->
						<h2 class="has-text-align-center" style="margin-bottom:24px">' . $title_placeholder . '</h2>
						<!-- /wp:heading -->

						<!-- wp:paragraph {"className":"mb-0","align":"center", "placeholder":"' . __( 'Enter description', 'planet4-blocks-backend' ) . '"} -->
						<p class="mb-0 has-text-align-center"></p>
						<!-- /wp:paragraph -->

						<!-- wp:group {"style":{"spacing":{"padding":{"top":"40px","bottom":"56px"}}},"className":"is-style-space-evenly","layout":{"type":"flex","allowOrientation":false}} -->
						<div class="wp-block-group is-style-space-evenly" style="padding-top:40px;padding-bottom:56px;">
						' . self::get_media_text_template() . '
						' . self::get_media_text_template() . '
						' . self::get_media_text_template() . '
						' . self::get_media_text_template() . '
						</div>
						<!-- /wp:group -->

						<!-- wp:buttons {"placeholder":"' . __( 'Enter text', 'planet4-blocks-backend' ) . '","layout":{"type":"flex","justifyContent":"center"}} -->
						<div class="wp-block-buttons">
						<!-- wp:button -->
						<div class="wp-block-button is-style-secondary"><a class="wp-block-button__link"></a></div>
						<!-- /wp:button --></div>
						<!-- /wp:buttons -->

					</div><!-- /wp:group -->
				</div><!-- /wp:group -->
			',
		];
	}
}
