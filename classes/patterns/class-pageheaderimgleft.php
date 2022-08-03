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
class PageHeaderImgLeft extends PageHeader {

	/**
	 * @var string
	 */
	protected static $title = 'Page Header with image on the left';

	/**
	 * @var string
	 */
	protected static $media_position = 'left';

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/page-header-img-left';
	}
}
