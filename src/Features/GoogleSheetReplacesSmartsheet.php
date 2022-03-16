<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class GoogleSheetReplacesSmartsheet extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'google_sheet_replaces_smartsheet';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Google Sheets replaces Smartsheet', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Toggle whether to use Google Sheets to fetch the list of analytics options.',
			'planet4-master-theme-backend'
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function show_toggle_production(): bool {
		return true;
	}
}
