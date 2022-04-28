<?php

namespace P4\MasterTheme\Features\ListingPages;

use P4\MasterTheme\Feature;

/**
 * @see description()
 */
class TagPagePagination extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'tag_page_pagination';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Tag page pagination', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __( 'Use the new paginated tag listing pages.', 'planet4-master-theme-backend' );
	}
}
