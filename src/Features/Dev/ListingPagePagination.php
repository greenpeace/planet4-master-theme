<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ListingPagePagination extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'listing_page_pagination';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Listing Page Pagination', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __( 'Use a paginated list of posts on tag pages.', 'planet4-master-theme-backend' );
	}
}
