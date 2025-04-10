<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the post tags from the Posts List blocks.
 */
class M044RemoveTagsFromPostsListMigration extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        $check_is_valid_block = function ($block) {
            return self::check_is_valid_block($block);
        };

        $transform_block = function ($block) {
            self::transform_block($block['innerBlocks']);
            return $block;
        };

        Utils\Functions::execute_block_migration(
            Utils\Constants::BLOCK_QUERY,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a Query Loop block.
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

        // Check if the block has a namespace.
        if (!isset($block['attrs']['namespace'])) {
            return false;
        }

        // Check if the block is a Posts List block.
        return $block['blockName'] === Utils\Constants::BLOCK_QUERY && $block['attrs']['namespace'] === Utils\Constants::BLOCK_POSTS_LIST;
    }

    /**
     * Remove the terms from the Posts List blocks, except categories.
     *
     * @param array $blocks - The current blocks array.
     * @return void
     */
    private static function transform_block(array &$blocks): void
    {
        foreach ($blocks as $key => &$block) {
            if (
                isset($block['blockName']) &&
                isset($block['attrs']) &&
                isset($block['attrs']['term']) &&
                $block['blockName'] === Utils\Constants::BLOCK_TERMS &&
                $block['attrs']['term'] !== 'category'
            ) {
                if (array_key_exists($key, $blocks)) {
                    unset($blocks[$key]);
                }
            }

            if (!isset($block['innerBlocks']) || !is_array($block['innerBlocks'])) {
                continue;
            }

            self::transform_block($block['innerBlocks']);
        }
    }
}
