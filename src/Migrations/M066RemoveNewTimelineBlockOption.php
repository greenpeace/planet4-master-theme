<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the "New Timeline Block" option from Planet 4 features.
 */
class M066RemoveNewTimelineBlockOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $features = get_option('planet4_features');
        unset($features['new_timeline_block']);
        update_option('planet4_features', $features);
    }
}
