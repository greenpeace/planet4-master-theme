<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Turn on the lazy youtube feature everywhere.
 */
class M002EnableLazyYoutube extends MigrationScript
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
        $features['lazy_youtube_player'] = true;
        update_option('planet4_features', $features);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
