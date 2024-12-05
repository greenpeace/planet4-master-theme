<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the Theme Editor feature flag from Planet 4 features.
 */
class M036RemoveEnFormOptions extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $check_is_valid_block = function ($block) {
            return self::check_is_valid_block($block);
        };

        $remove_block = function ($block) {
            return self::remove_block($block);
        };

        // Remove ENForm block from posts.
        Utils\Functions::execute_block_migration(
            Utils\Constants::BLOCK_ENFORM,
            $check_is_valid_block,
            $remove_block,
        );

        // Unset ENForm feature flag.
        $features = get_option('planet4_features');
        unset($features[ 'feature_engaging_networks' ]);
        update_option('planet4_features', $features);

        // Unset ENForm credentials.
        delete_option('p4en_main_settings');

        // Delete ENForm forms.
        $posts = get_posts([
            'post_type' => 'p4en_form',
        ]);
        foreach ($posts as $post) {
            echo 'Parsing post ', $post->ID, ': ', $post->post_title, '\n';
            wp_delete_post($post->ID, true);
        }
    }

    /**
     * Check whether a block is a ENForm block.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        // Check if the block is valid.
        if (!is_array($block)) {
            return false;
        }

        // Check if the block has a 'blockName' key.
        if (!isset($block['blockName'])) {
            return false;
        }

        // Check if the block is a ENForm block. If not, abort.
        return $block['blockName'] === Utils\Constants::BLOCK_ENFORM;
    }

    /**
     * Removes the ENForm block.
     *
     * @param array $block - A block data array.
     *
     */
    private static function remove_block(array $block): array
    {
        unset($block);
        return [];
    }
}
