<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\Settings\Features;
use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;

/**
 * Move feature settings to a separate database record. This allows disabling translation on those settings.
 */
class M006MoveFeaturesToSeparateOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     */
    protected static function execute(MigrationRecord $record): void
    {
        // This should run for the default language during post deploy.
        $settings = get_option(Settings::KEY);
        $feature_fields = Features::get_fields();
        $feature_values = [];

        foreach ($feature_fields as $field) {
            $id = $field['id'];

            $value = $settings[ $id ] ?? null;
            if (!$value) {
                continue;
            }

            $feature_values[ $id ] = $value;
        }
        update_option(Features::OPTIONS_KEY, $feature_values);
        $record->add_log('Successfully migrated feature settings: ' . "\n" . print_r($feature_values, true)); //phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_print_r
    }
}
