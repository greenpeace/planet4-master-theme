<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Replace the special characters in the Post List block description that were not correctly migrated.
 */
class M040FixSpecialCharactersInPostsListBlock extends MigrationScript
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
            return self::transform_block($block);
        };

        Utils\Functions::execute_block_migration(
            Utils\Constants::BLOCK_QUERY,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a Query/Post List block.
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

        // Check if the block is a Query block. If not, abort.
        if ($block['blockName'] !== Utils\Constants::BLOCK_QUERY) {
            return false;
        }

        // Check if the block contains a namespace. If not, abort.
        if (!isset($block['attrs']['namespace'])) {
            return false;
        }

        // Check if the block is a Post List block.
        return $block['attrs']['namespace'] === Utils\Constants::BLOCK_POSTS_LIST;
    }

    /**
     * Replace the special characters in the block description.
     *
     * @param array $block - The current Query block.
     * @return array - The block with the fixes.
     */
    private static function transform_block(array $block): array
    {
        foreach ($block['innerBlocks'] as &$inner_block) {
            if (!isset($inner_block['blockName'])) {
                continue;
            }

            if ($inner_block['blockName'] !== Utils\Constants::BLOCK_PARAGRAPH) {
                continue;
            }

            $content = $inner_block['innerHTML'];

            // Only replace standalone "u003c", "u003e", "u0022" (not inside quotes or escape sequences)
            $content = preg_replace('/(?<!\\\\)u003c/', '<', $content);
            $content = preg_replace('/(?<!\\\\)u003e/', '>', $content);
            $content = preg_replace('/(?<!\\\\)u0022/', '"', $content);

            $inner_block['innerHTML'] = $content;
            $inner_block['innerContent'][0] = $content;
        }
        unset($inner_block);
        return $block;
    }
}
