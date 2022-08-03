<?php
/**
 * Page Header pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

/**
 * Class Page Header.
 *
 * @package P4GBKS\Patterns
 */
class PageHeader extends Block_Pattern {

	/**
	 * @var string
	 */
	protected static $title = 'Page Header with image on the right';

	/**
	 * @var string
	 */
	protected static $media_position = 'right';

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/page-header-img-right';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		$classname  = 'is-pattern-p4-page-header';
		$media_link = esc_url( get_template_directory_uri() ) . '/images/placeholders/placeholder-546x415.jpg';
		$pos_class  = 'left' === static::$media_position ? ''
			: 'has-media-on-the-' . static::$media_position;

		return [
			'title'      => __( static::$title, 'planet4-blocks-backend' ), // phpcs:ignore
			'categories' => [ 'page-headers' ],
			'content'    => '
				<!-- wp:media-text {"align":"full","mediaPosition":"' . static::$media_position . '","mediaType":"image","imageFill":false,"className":"' . $classname . '"} -->
				<div class="wp-block-media-text alignfull ' . $pos_class . ' is-stacked-on-mobile ' . $classname . '">

					<figure class="wp-block-media-text__media">
						<img src="' . $media_link . '" alt=""/>
					</figure>

					<div class="wp-block-media-text__content">
						<!-- wp:group -->
						<div class="wp-block-group">
							<!-- wp:heading {"level":1,"placeholder":"' . __( 'Enter title', 'planet4-blocks-backend' ) . '","backgroundColor":"white"} -->
							<h1 class="has-white-background-color has-background"></h1>
							<!-- /wp:heading -->
						</div>
						<!-- /wp:group -->

						<!-- wp:paragraph {"placeholder":"' . __( 'Enter description', 'planet4-blocks-backend' ) . '","style":{"typography":{"fontSize":"1.25rem"}}} -->
						<p style="font-size:1.25rem"></p>
						<!-- /wp:paragraph -->

						<!-- wp:buttons -->
						<div class="wp-block-buttons">
							<!-- wp:button {"className":"is-style-cta"} /-->
						</div>
						<!-- /wp:buttons -->
					</div>
				</div>
				<!-- /wp:media-text -->
			',
		];
	}
}
