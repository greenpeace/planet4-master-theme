<?php

namespace P4\MasterTheme\Migrations;

use DateTime;

/**
 * Information on a migration run.
 */
final class MigrationRecord {
	/**
	 * @var string The id of the script.
	 */
	private $migration_id;

	/**
	 * @var DateTime The time the script started.
	 */
	private $start_time;

	/**
	 * @var DateTime The time the script completed.
	 */
	private $end_time;

	/**
	 * @var bool Whether the script succeeded.
	 */
	private $successful;

	/**
	 * @var string[] Log messages which can be added by the running script.
	 */
	private $logs = [];

	/**
	 * Get a new record with the current time as start time, to be called just before starting the run.
	 *
	 * @param string $migration_id The id of the migration being recorded.
	 *
	 * @return self A new record.
	 */
	public static function start( string $migration_id ): self {
		$record = new self();

		$record->migration_id = $migration_id;
		$record->start_time   = new DateTime();

		return $record;
	}

	/**
	 * Add a log messages.
	 *
	 * @param string $message a log messages.
	 */
	public function add_log( string $message ): void {
		$this->logs[] = $message;
	}

	/**
	 * Mark script as failed.
	 */
	public function fail(): void {
		$this->successful = false;
	}

	/**
	 * Mark script as success.
	 */
	public function success(): void {
		$this->successful = true;
	}

	/**
	 * Mark script as done.
	 */
	public function done(): void {
		$this->end_time = new DateTime();
	}

	/**
	 * When the script started.
	 *
	 * @return DateTime When the script started.
	 */
	public function get_start_time(): DateTime {
		return $this->start_time;
	}

	/**
	 * When the script completed.
	 *
	 * @return DateTime When the script completed.
	 */
	public function get_end_time(): ?DateTime {
		return $this->end_time;
	}

	/**
	 * The id of the script
	 *
	 * @return string The id of the script
	 */
	public function get_migration_id(): string {
		return $this->migration_id;
	}

	/**
	 * Whether the run succeeded.
	 *
	 * @return bool Whether the run succeeded.
	 */
	public function was_success(): ?bool {
		return $this->successful;
	}

	/**
	 * Get the log messages.
	 *
	 * @return string[] The log messages.
	 */
	public function get_logs(): array {
		return $this->logs;
	}
}
