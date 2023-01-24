<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Features;
use P4\MasterTheme\Settings;

/**
 * Remove the Smartsheet options from Planet 4 settings and features.
 */
class M011RemoveSmartsheetOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        // Toggle between GoogleSheet and SmartSheet.
        $options = get_option(Features::OPTIONS_KEY);
        unset($options['google_sheet_replaces_smartsheet']);
        update_option(Features::OPTIONS_KEY, $options);

        // Smartsheet ID and legacy toggle.
        $options = get_option(Settings::KEY);
        unset($options['analytics_local_smartsheet_id']);
        unset($options['google_sheet_replaces_smartsheet']);
        update_option(Settings::KEY, $options);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
