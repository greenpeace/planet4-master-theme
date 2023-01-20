<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings\InformationArchitecture;

/**
 * Remove the Dropdown navigation menus feature flag from Planet 4 Information Architecture settings.
 */
class M015RemoveListingPagesBackgroundImages extends MigrationScript {
	/**
	 * Perform the actual migration.
	 *
	 * @param MigrationRecord $record Information on the execution, can be used to add logs.
	 *
	 * @return void
	 */
	protected static function execute( MigrationRecord $record ): void {
		// Listing pages background image feature flag.
		$options = get_option( InformationArchitecture::OPTIONS_KEY );
		unset( $options['hide_listing_pages_background'] );
		update_option( InformationArchitecture::OPTIONS_KEY, $options );
	}
}
