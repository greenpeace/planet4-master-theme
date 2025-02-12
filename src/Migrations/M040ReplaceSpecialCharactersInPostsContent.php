<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Replace special characters ("u003c", "u003e", "u0022") in the posts content.
 */
class M040ReplaceSpecialCharactersInPostsContent extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        echo 'Replacing special characters in posts content...', "\n"; // phpcs:ignore

        $args = [
            'post_type' => get_post_types(['public' => true], 'names'),
            'post_status' => 'any',
            'posts_per_page' => -1,
        ];

        $results = get_posts($args) ?? [];

        foreach ((array) $results as $post) {
            if (empty($post->post_content)) {
                continue;
            }

            $current_post_id = $post->ID;

            echo 'Parsing post ', $current_post_id, "\n"; // phpcs:ignore

            $post_content = $post->post_content;

            // Only replace standalone "u003c", "u003e", "u0022" (not inside quotes or escape sequences)
            $post_content = preg_replace('/(?<!\\\\)u003c/', '<', $post_content);
            $post_content = preg_replace('/(?<!\\\\)u003e/', '>', $post_content);
            $post_content = preg_replace('/(?<!\\\\)u0022/', '"', $post_content);

            $post_args = [
                'ID' => $current_post_id,
                'post_content' => $post_content,
            ];

            wp_update_post(wp_slash($post_args));
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
