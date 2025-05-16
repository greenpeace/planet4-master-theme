<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Add tags back in Posts List block (visible in list layout only).
 */
class M048AddTagsBackInPostsListBlock extends MigrationScript
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
            self::transform_block($block['innerBlocks'], $block['attrs']['namespace']);
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
     * Add tags back to the Posts List block, after the P4 taxonomy block.
     */
    private static function transform_block(array &$blocks, string $type): void
    {
        $inserted = false; // Flag to ensure insertion happens only once

        foreach ($blocks as $key => &$block) {
            if (
                isset($block['blockName']) &&
                $block['blockName'] === Utils\Constants::P4_OTHER_BLOCKS['breadcrumb'] &&
                !$inserted
            ) {
                $new_block = Utils\Functions::create_new_block(
                    Utils\Constants::BLOCK_TERMS,
                    ['term' => 'post_tag', 'separator' => ' ']
                );
                array_splice($blocks, $key + 1, 0, [$new_block]);
                var_dump($blocks);
                $inserted = true;
            }
            if (!isset($block['innerBlocks']) || !is_array($block['innerBlocks'])) {
                continue;
            }
            self::transform_block($block['innerBlocks'], $type);
        }
    }
}
