<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the Purge on feature change option from Planet 4 Features.
 */
class M030RemovePurgeOnFeatureChangeOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        // Purge on feature change feature flag.
        $features = get_option('planet4_features');
        unset($features[ 'purge_on_feature_changes' ]);
        update_option('planet4_features', $features);
    }
}
