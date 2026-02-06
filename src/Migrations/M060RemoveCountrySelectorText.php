<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;

/**
 * Remove the "Country Selector text" options from Planet 4 settings.
 */
class M060RemoveCountrySelectorText extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $p4_settings = get_option(Settings::KEY);
        unset($p4_settings['website_navigation_title']);
        update_option(Settings::KEY, $p4_settings);
    }
}
