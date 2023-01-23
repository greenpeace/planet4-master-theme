<?php

namespace P4\MasterTheme;

use DateTime;

/**
 * Information on a migration run.
 */
final class MigrationRecord
{
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
    private $success;

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
    public static function start(string $migration_id): self
    {
        $record = new self();

        $record->migration_id = $migration_id;
        $record->start_time = new DateTime();

        return $record;
    }

    /**
     * Add a log messages.
     *
     * @param string $message a log messages.
     */
    public function add_log(string $message): void
    {
        $this->logs[] = $message;
    }

    /**
     * Mark this migration as succeeded.
     */
    public function succeed(): void
    {
        $this->success = true;
    }

    /**
     * Mark script as failed.
     *
     * @param string $message Reason for failure.
     */
    public function fail(string $message): void
    {
        $this->success = false;
        $this->add_log($message);
    }

    /**
     * Mark script as done.
     */
    public function done(): void
    {
        $this->end_time = new DateTime();
    }

    /**
     * Record a migration run in the log.
     *
     * @return array Array with instance's data.
     */
    public function to_log_entry(): array
    {
        return [
            'id' => $this->migration_id,
            'start_time' => $this->start_time,
            'end_time' => $this->end_time,
            'success' => $this->success,
            'logs' => $this->logs,
        ];
    }

    /**
     * The id of the script
     *
     * @return string The id of the script
     */
    public function get_migration_id(): string
    {
        return $this->migration_id;
    }

    /**
     * Whether the run succeeded.
     *
     * @return bool Whether the run succeeded.
     */
    public function was_success(): ?bool
    {
        return $this->success;
    }
}
