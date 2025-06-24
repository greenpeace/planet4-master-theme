<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Query;

/**
 * Replace special characters in the posts content.
 */
class M051ReplaceSpecialCharactersInPostsContent extends MigrationScript
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

        $post_types = array_diff(get_post_types(['public' => true], 'names'), ['archive']);
        $paged = 1;
        $per_page = 30;

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
                $content = get_post_field('post_content', $post_id);

                if (empty($content)) {
                    continue;
                }

                $updated_content = preg_replace_callback(
                    '/\\\\?u([0-9a-fA-F]{4})/',
                    fn ($matches) => html_entity_decode('&#x' . $matches[1] . ';', ENT_QUOTES, 'UTF-8'),
                    $content
                );

                if ($updated_content === $content) {
                    continue;
                }

                $result = wp_update_post([
                    'ID' => $post_id,
                    'post_content' => wp_slash($updated_content),
                ]);

                if (!is_wp_error($result) && $result > 0) {
                    echo "Post updated successfully: ID ", $post_id, "\n"; // phpcs:ignore
                } else {
                    echo "Failed to update post: ID ", $post_id, "\n"; // phpcs:ignore
                }
            }

            $paged++;
            wp_reset_postdata();
        } while ($query->found_posts > ($paged - 1) * $per_page);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
