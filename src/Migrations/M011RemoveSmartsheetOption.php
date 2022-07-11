<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Features;

/**
 * Remove the "enchanced donate button" option from Planet 4 settings.
 */
class M011RemoveSmartsheetOption extends MigrationScript {
	/**
	 * Perform the actual migration.
	 *
	 * @param MigrationRecord $record Information on the execution, can be used to add logs.
	 *
	 * @return void
	 */
	protected static function execute( MigrationRecord $record ): void {
		$options = get_option( Features::OPTIONS_KEY );
		unset( $options['google_sheet_replaces_smartsheet'] );
		update_option( Features::OPTIONS_KEY, $options );
	}
}
