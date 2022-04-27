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
		return __( 'Google Sheets instead of Smartsheet', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Use Google Sheets to fetch the <a href="https://jira.greenpeace.org/browse/PLANET-6452" target="_blank">local</a> and <a href="https://jira.greenpeace.org/browse/PLANET-6451" target="_blank">global</a> projects.',
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
