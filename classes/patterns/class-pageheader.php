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
		if ( empty( $params['mediaPosition'] ) ) {
			$params['mediaPosition'] = static::$media_position;
		}

		return [
			'title'      => __( static::$title, 'planet4-blocks-backend' ), // phpcs:ignore
			'categories' => [ 'page-headers' ],
			'content'    => '
				<!-- wp:planet4-block-templates/page-header ' . wp_json_encode( $params, \JSON_FORCE_OBJECT ) . ' /-->
			',
		];
	}
}
