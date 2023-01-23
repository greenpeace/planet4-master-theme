<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;

/**
 * Remove the "enchanced donate button" option from Planet 4 settings.
 */
class M007RemoveEnhancedDonateButtonOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     */
    protected static function execute(MigrationRecord $record): void
    {
        $options = get_option(Settings::KEY);
        unset($options['donate_btn_visible_on_mobile']);
        update_option(Settings::KEY, $options);
    }
}
