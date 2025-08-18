<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Update the limit of post revisions.
 */
class M056ReplaceIRLtoOfflineResistanceHub extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        global $wpdb;

        $sql = '
            UPDATE wp_posts
            SET meta_value="%2$s"
            WHERE meta_key="actions_task_type" AND meta_value="%1$s"
        ';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $prepared_sql = $wpdb->prepare($sql, [ "irl", "offline" ]);
        $wpdb->query($prepared_sql);
    }
}
