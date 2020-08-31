<?php

namespace P4\MasterTheme;

use Exception;
use P4\MasterTheme\Exception\MigrationFailed;
use P4\MasterTheme\Migrations\MigrationRecord;

/**
 * A migration that can be recorded.
 */
abstract class Migration {
	/**
	 * Get a unique identifier, achieved here by using the FQCN.
	 *
	 * @return string The unique identifier.
	 */
	public static function get_id(): string {
		return static::class;
	}

	/**
	 * Perform the actual migration.
	 *
	 * @param MigrationRecord $record
	 *
	 * @return void
	 */
	abstract public static function execute(MigrationRecord $record): void;

	/**
	 * Log the time and run the migration.
	 *
	 * @return MigrationRecord Data on the migration run.
	 * @throws MigrationFailed If the migration encounters an error.
	 */
	public static function run(): MigrationRecord {
		$record = MigrationRecord::start( static::class );

		try {
			static::execute( $record );
		} catch ( Exception $exception ) {
			throw new MigrationFailed(
				'Migration ' . $record->get_migration_id() . ' failed. Message: ' . $exception->getMessage(),
				null,
				$exception
			);
		}

		$record->done();

		return $record;
	}
}
