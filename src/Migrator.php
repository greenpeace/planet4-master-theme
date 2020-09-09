<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Migrations\M001EnableEnFormFeature;

/**
 * Run any new migration scripts and record results in the log.
 */
class Migrator {

	/**
	 * Run any new migration scripts and record results in the log.
	 */
	public static function migrate(): void {

		// Fetch migration script ids that have run from WP option.
		$log = MigrationLog::from_wp_options();

		/**
		 * @var MigrationScript[] $scripts
		 */
		$scripts = [
			M001EnableEnFormFeature::class,
		];

		// Loop migrations and run those that haven't run yet.
		foreach ( $scripts as $script ) {
			if ( $log->already_ran( $script::get_id() ) ) {
				continue;
			}

			$record = $script::run();
			$log->add( $record );
		}

		$log->persist();
	}
}
