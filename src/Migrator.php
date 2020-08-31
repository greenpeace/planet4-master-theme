<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Migrations\M001EnableEnFormFeature;

/**
 * Run any new migrations and record them in the log.
 */
class Migrator {

	/**
	 * Run any new migrations and record them in the log.
	 */
	public static function migrate() {

		// Fetch migration ids that have run from WP option.
		$log = MigrationLog::from_wp_options();

		/**
		 * @var Migration[] $migrations
		 */
		$migrations = [
			M001EnableEnFormFeature::class,
		];

		// Loop migrations and run those that haven't run yet.
		foreach ( $migrations as $migration ) {
			if ( $log->already_ran( $migration::get_id() ) ) {
				continue;
			}

			$record = $migration::run();
			$log->add( $record );
		}

		$log->persist();
	}
}
