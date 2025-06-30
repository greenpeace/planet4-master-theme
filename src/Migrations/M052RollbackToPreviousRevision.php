<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Query;

/**
 * Replace special characters in the posts content.
 */
class M052RollbackToPreviousRevision extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        echo "Post revision restore migration in progress...\n"; // phpcs:ignore

        $post_types = array_diff(get_post_types(['public' => true], 'names'), ['archive']);
        $paged = 1;
        $per_page = 30;

        // Date of targeted Post revision.
        $revision_date = "2025-06-24"; // YYYY-MM-DD format

        do {
            $query = new WP_Query([
                'post_type' => $post_types,
                'posts_per_page' => $per_page,
                'paged' => $paged,
                'post_status' => 'any',
                'fields' => 'ids',
            ]);

            if (!$query->have_posts()) {
                break;
            }

            foreach ($query->posts as $post_id) {
                // Rollback to previous revision created by migration script.
                self::rollback_to_previous_revision($post_id, $revision_date);
            }

            $paged++;
            wp_reset_postdata();
        } while ($query->found_posts > ($paged - 1) * $per_page);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Rollback the latest revision(migration script revision) created by previous migration script.
     * @param int $post_id The post ID
     * @param string $revision_date Date Revision date
     */
    private static function rollback_to_previous_revision(int $post_id, string $revision_date): void
    {
        // Fetch last 5 revisions regardless of date, cause we need to restore a revision before the targeted revision.
        $revisions = wp_get_post_revisions($post_id, [
            'posts_per_page' => 5,
            'order' => 'DESC',
        ]);
        if (!$revisions) {
            return;
        }

        $rollback_to_revision = 0;
        $prev_revision = null;
        $num_revision_rollback = 0;

        foreach ($revisions as $revision) {
            // Check if previous revision existed and has post_author 0.
            if ($prev_revision && 0 == $prev_revision->post_author) { // phpcs:ignore
                $rollback_to_revision = $revision->ID;
                break;
            }
            $prev_revision = $revision;
            $num_revision_rollback++;
        }

        // Match the prev_revision date & number of revision rollback count.
        if (1 === $num_revision_rollback && $rollback_to_revision && date('Y-m-d', strtotime($prev_revision->post_date)) == $revision_date) { // phpcs:ignore
            $result = wp_restore_post_revision($rollback_to_revision);
            if (!is_wp_error($result) && $result > 0) {
                echo "Post revision restore successfully: ID ", $post_id, " revision ID:", $rollback_to_revision, " count:", $num_revision_rollback, "\n"; // phpcs:ignore
            } else {
                echo "Failed to restore post: ID ", $post_id, "\n"; // phpcs:ignore
            }
        } else {
            if ($num_revision_rollback > 1 && $rollback_to_revision && date('Y-m-d', strtotime($prev_revision->post_date)) == $revision_date) { // phpcs:ignore
                echo "Post revision restore skipped: ID ", $post_id, " revision ID:", $rollback_to_revision, " count:", $num_revision_rollback, "\n"; // phpcs:ignore
            }
        }
    }
}
