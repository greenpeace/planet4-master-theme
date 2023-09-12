<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Update the limit of post revisions.
 */
class M022UpdatePostRevisions extends MigrationScript
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

        if (!get_option('revisions_to_keep')) {
            // If not, set 20 revisions by default
            $MAX_REVISIONS = 20;
            update_option('revisions_to_keep', $MAX_REVISIONS);
        } else {
            $MAX_REVISIONS = get_option('revisions_to_keep');
        }

        $wpdb->query('SET @rownumber:=0');
        $wpdb->query('SET @postparent:=NULL');
        $sql = '
            DELETE FROM wp_posts
            WHERE ID IN (
                SELECT ID
                FROM (
                    SELECT
                        post_parent,
                        ID,
                        post_date,
                        @rownumber:=(IF(@postparent=post_parent, @rownumber:=@rownumber+1, 1)) as rwn,
                        @postparent:=post_parent
                    FROM (
                        SELECT
                            ID,
                            post_parent,
                            post_date
                        FROM wp_posts
                        WHERE post_parent IN (
                            SELECT post_parent
                            FROM wp_posts
                            WHERE post_type = "%2$s"
                            GROUP BY post_parent
                            HAVING count(ID) > %1$s
                        ) AND post_type = "%2$s"
                        ORDER BY post_date ASC
                    ) AS r
                ) AS d
                WHERE d.rwn > %1$s
            );
        ';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $prepared_sql = $wpdb->prepare($sql, [ $MAX_REVISIONS, "revision" ]);
        $wpdb->query($prepared_sql);
    }
}
