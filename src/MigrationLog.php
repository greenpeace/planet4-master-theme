<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Migrations\MigrationRecord;

/**
 * A log of all migrations that have run, saved as a WP option.
 */
class MigrationLog {
	/**
	 * The WP option key.
	 */
	private const OPTION_KEY = 'planet4_migrations';

	/**
	 * @var array[] An entry for each done migration.
	 */
	private $done_migrations;

	/**
	 * Get the log from the WP options.
	 *
	 * @return static The log.
	 */
	public static function from_wp_options(): self {
		$done_migrations = get_option( self::OPTION_KEY, [] );

		$log = new self();

		$log->done_migrations = $done_migrations;

		return $log;
	}

	/**
	 * Check whether a migration has already run.
	 *
	 * @param string $migration_id The migration to check.
	 *
	 * @return bool Whether said migration already ran.
	 */
	public function already_ran( string $migration_id ): bool {
		foreach ( $this->done_migrations as $migration ) {
			if ( $migration['id'] === $migration_id && $migration['success'] ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Record a migration run in the log.
	 *
	 * @param MigrationRecord $record
	 */
	public function add( MigrationRecord $record ): void {
		$entry = [
			'id'         => $record->get_migration_id(),
			'start_time' => $record->get_start_time(),
			'end_time'   => $record->get_end_time(),
			'success'    => $record->was_success(),
			'logs'       => $record->get_logs(),
		];

		$this->done_migrations[] = $entry;
	}

	/**
	 * Save the state of the log in the WP options.
	 */
	public function persist(): void {
		add_option( self::OPTION_KEY, $this->done_migrations );
	}
}
