<?php

namespace P4\MasterTheme\Features\ListingPages;

use P4\MasterTheme\Feature;

/**
 * @see description()
 */
class AuthorPagePagination extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'author_page_pagination';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Author page pagination', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __( 'Use the new paginated author listing pages.', 'planet4-master-theme-backend' );
	}
}
