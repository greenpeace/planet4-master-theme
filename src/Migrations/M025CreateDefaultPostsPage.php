<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Add default for Posts page.
 */
class M025CreateDefaultPostsPage extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $existing_posts_page = get_option('page_for_posts');
        if ($existing_posts_page) {
            return;
        }

        $new_posts_page = array(
            'post_type' => 'page',
            'post_title' => __('News & Stories', 'planet4-master-theme-backend'),
            'post_excerpt' => __('Read the latest updates.', 'planet4-master-theme-backend'),
            'post_status' => 'publish',
        );

        $new_posts_page_id = wp_insert_post($new_posts_page);
        update_option('page_for_posts', $new_posts_page_id);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
