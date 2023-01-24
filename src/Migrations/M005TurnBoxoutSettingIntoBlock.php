<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Post;

/**
 * Add the Take Action Boxout block as a block to all posts that use it with the settings field, and remove field.
 */
class M005TurnBoxoutSettingIntoBlock extends MigrationScript
{
    private const BOXOUT_META_KEY = 'p4_take_action_page';

    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $args = [
            'posts_per_page' => - 1,
            'meta_query' => [
                [
                    'key' => self::BOXOUT_META_KEY,
                    'value' => '',
                    'compare' => 'NOT IN',
                ],
            ],
        ];

        $tab_posts = get_posts($args);
        echo 'Converting ' . count($tab_posts) . " posts with Take Action Boxout setting to blocks.\n";

        foreach ($tab_posts as $tab_post) {
            // If the post already has a TAB inside, that one took precedence before and only meta cleanup needed.
            if (! has_block('planet4-blocks/take-action-boxout', $tab_post)) {
                self::append_boxout_from_meta($tab_post);
            }
            delete_post_meta($tab_post->ID, self::BOXOUT_META_KEY);
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Add the Take Action Boxout block that is in the meta to the end of the post content.
     *
     * @param WP_Post $post A post that has a TAB set through its meta.
     */
    private static function append_boxout_from_meta(WP_Post $post): void
    {
        $attrs = [
            'take_action_page' => (int) get_post_meta($post->ID, self::BOXOUT_META_KEY, true),
        ];
        $boxout = '<!-- wp:planet4-blocks/take-action-boxout ' . wp_json_encode($attrs, JSON_UNESCAPED_SLASHES) . ' /-->';

        $args = [
            'ID' => $post->ID,
            'post_content' => $post->post_content . "\n" . $boxout,
        ];

        wp_update_post($args);
    }
}
