<?php

namespace P4\MasterTheme\Features\ListingPages;

use P4\MasterTheme\Feature;

/**
 * @see description()
 */
class CategoryPagePagination extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'category_page_pagination';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Category page pagination', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __( 'Use the new paginated category listing pages.', 'planet4-master-theme-backend' );
	}
}
