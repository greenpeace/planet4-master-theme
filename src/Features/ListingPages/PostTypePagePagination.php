<?php

namespace P4\MasterTheme\Features\ListingPages;

use P4\MasterTheme\Feature;

/**
 * @see description()
 */
class PostTypePagePagination extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'post_type_page_pagination';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Post type page pagination', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __( 'Use the new paginated post type listing pages.', 'planet4-master-theme-backend' );
	}
}
