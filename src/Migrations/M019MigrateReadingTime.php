<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings\ReadingTime;

class M019MigrateReadingTime extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        $value = planet4_get_option('reading_time_wpm');

        $options = get_option('planet4_option');
        unset($options['reading_time_wpm']);
        update_option('planet4_options', $options);

        if (!$value) {
            return;
        }

        update_option(ReadingTime::KEY, $value);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
