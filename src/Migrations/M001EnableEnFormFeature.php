<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\Features;
use P4\MasterTheme\Migration;
use P4\MasterTheme\Settings;

/**
 * Turn on the EN form feature everywhere.
 */
class M001EnableEnFormFeature extends Migration {

	/**
	 * @inheritDoc
	 */
	public static function execute( MigrationRecord $record ): void {
		$settings = get_option( Settings::KEY, [] );

		$settings[ Features::ENGAGING_NETWORKS ] = 'on';
		update_option( Settings::KEY, $settings );
		$record->add_log('This is a message from your upgrade script.');
		$record->add_log('This is a second message from your upgrade script.');

		if ( $settings['Something terribly wrong in the db'] ) {
			$record->fail();
		} else {
			$record->success();
		}
	}
}
