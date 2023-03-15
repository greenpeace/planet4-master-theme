<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;

/**
 * Update the limit of post revisions.
 */
class M018UpdatePostRevisions extends MigrationScript
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

        $MAX_REVISIONS = (int) get_option('revisions_to_keep');

        $sql = '
            SELECT count(ID) AS total_revisions, ID, post_parent
            FROM wp_posts
            WHERE post_type = "revision"
            GROUP BY post_parent
            ORDER BY post_date ASC';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $prepared_sql = $wpdb->prepare($sql);
        $results = $wpdb->get_results($prepared_sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        foreach ((array) $results as $post) {
            if ((int) $post->total_revisions <= $MAX_REVISIONS) {
                continue;
            }

            echo "\nDelete " . $MAX_REVISIONS . " of " . $post->total_revisions . " revisions FROM " . $post->ID;
            $sql = '
                DELETE FROM wp_posts
                WHERE post_type = "revision"
                AND post_parent = ' . $post->post_parent . '
                ORDER BY post_date DESC limit ' . $MAX_REVISIONS;
            $wpdb->get_results($wpdb->prepare($sql));
        }
    }
}
