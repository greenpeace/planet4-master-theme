<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\InformationArchitecture;

/**
 * @see description()
 */
class HideListingPagesBackground extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'hide_listing_pages_background';
	}


	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Listing pages background', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Remove background image and skewed overlay on all listing pages.',
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
