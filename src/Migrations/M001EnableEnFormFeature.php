<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\Features;
use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;

/**
 * Turn on the EN form feature everywhere.
 */
class M001EnableEnFormFeature extends MigrationScript {

	/**
	 * Perform the actual migration.
	 *
	 * @param MigrationRecord $record Information on the execution, can be used to add logs.
	 *
	 * @return void
	 */
	protected static function execute( MigrationRecord $record ): void {
		$settings = get_option( Settings::KEY, [] );

		$settings[ Features::ENGAGING_NETWORKS ] = 'on';
		update_option( Settings::KEY, $settings );
	}
}
