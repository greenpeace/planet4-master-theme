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
	public static function run(): void {
		$settings = get_option( Settings::KEY, [] );

		$settings[ Features::ENGAGING_NETWORKS ] = 'on';
		update_option( Settings::KEY, $settings );
	}
}
