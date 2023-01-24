<?php

namespace P4\MasterTheme;

/**
 * A log of all migrations that have run, saved as a WP option.
 */
class MigrationLog
{
    /**
     * The WP option key.
     */
    private const OPTION_KEY = 'planet4_migrations';

    /**
     * @var array[] An entry for each done migration.
     */
    private array $done_migrations = [];

    /**
     * Get the log from the WP options.
     *
     * @return static The log.
     */
    public static function from_wp_options(): self
    {
        $done_migrations = get_option(self::OPTION_KEY, []);

        $log = new self();

        if ($done_migrations) {
            $log->done_migrations = $done_migrations;
        }

        return $log;
    }

    /**
     * Check whether a migration has already run.
     *
     * @param string $migration_id The migration to check.
     *
     * @return bool Whether said migration already ran.
     */
    public function already_ran(string $migration_id): bool
    {
        if (empty($this->done_migrations)) {
            return false;
        }

        foreach ($this->done_migrations as $migration) {
            if ($migration['id'] === $migration_id && $migration['success']) {
                return true;
            }
        }

        return false;
    }

    /**
     * Add a migration record to the log.
     *
     * @param MigrationRecord $record The record to log.
     */
    public function add(MigrationRecord $record): void
    {
        $this->done_migrations[] = $record->to_log_entry();
    }

    /**
     * Save the state of the log in the WP options.
     */
    public function persist(): void
    {
        update_option(self::OPTION_KEY, $this->done_migrations);
    }
}
