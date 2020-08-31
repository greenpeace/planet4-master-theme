<?php

namespace P4\MasterTheme\Migrations;

use DateTime;

class MigrationRecord {
	private $migration_id;
	private $start_time;
	private $end_time;
	private $successful;
	private $logs = [];

	public static function start( string $migration_id ): self {
		$record = new self();

		$record->migration_id = $migration_id;
		$record->start_time   = new DateTime();

		return $record;
	}

	public function add_log( string $message ): void {
		$this->logs[] = $message;
	}

	public function fail(): void {
		$this->successful = false;
	}

	public function success(): void {
		$this->successful = true;
	}

	public function done(): void {
		$this->end_time = new DateTime();
	}

	public function get_start_time(): DateTime {
		return $this->start_time;
	}

	public function get_end_time(): ?DateTime {
		return $this->end_time;
	}

	/**
	 * @return mixed
	 */
	public function get_migration_id(): string {
		return $this->migration_id;
	}

	public function was_success(): ?bool {
		return $this->successful;
	}

	public function get_logs(): array {
		return $this->logs;
	}
}
