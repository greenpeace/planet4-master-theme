<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the subtitle meta custom field.
 */
class M072RemoveSubtitleMetaCustomField extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        global $wpdb;

        $sql = '
			SELECT COUNT(*)
            FROM wp_postmeta
            WHERE meta_key="p4_subtitle"
            AND meta_value<>""';

            // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
            $results = $wpdb->get_results($sql);

        if ($results[0]->{'COUNT(*)'} !== 0) {
            echo "p4_subtitle metadata is still used in some posts.";
            return;
        }

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $wpdb->query('DELETE FROM wp_postmeta WHERE meta_key="p4_subtitle"');
        echo "Deleted p4_subtitle custom field through wp_postmeta table.";
    }
}
