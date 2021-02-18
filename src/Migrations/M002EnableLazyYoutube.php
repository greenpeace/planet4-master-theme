<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\Features;
use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;

/**
 * Turn on the lazy youtube feature everywhere.
 */
class M002EnableLazyYoutube extends MigrationScript {

	/**
	 * Perform the actual migration.
	 *
	 * @param MigrationRecord $record Information on the execution, can be used to add logs.
	 *
	 * @return void
	 */
	protected static function execute( MigrationRecord $record ): void {
		$settings = get_option( Settings::KEY, [] );

		$settings[ Features::LAZY_YOUTUBE_PLAYER ] = 'on';
		update_option( Settings::KEY, $settings );
	}
}
