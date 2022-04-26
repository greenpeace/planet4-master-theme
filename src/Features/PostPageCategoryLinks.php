<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\InformationArchitecture;

/**
 * @see description()
 */
class PostPageCategoryLinks extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'post_page_category_links';
	}


	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Post Page Category Links', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'On post pages, link to the categories, instead of a page that has the same category ("issue pages").',
			'planet4-master-theme-backend'
		);
	}

	/**
	 * @inheritDoc
	 */
	protected static function options_key(): string {
		return InformationArchitecture::OPTIONS_KEY;
	}

	/**
	 * @inheritDoc
	 */
	public static function show_toggle_production(): bool {
		return true;
	}
}
