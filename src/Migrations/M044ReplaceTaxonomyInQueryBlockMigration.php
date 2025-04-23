<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Replace the Post Terms blocks with the Taxonomy Breadcrumb block in Posts List and Actions List.
 */
class M044ReplaceTaxonomyInQueryBlockMigration extends MigrationScript
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
        return $block['blockName'] === Utils\Constants::BLOCK_QUERY &&
            (
                $block['attrs']['namespace'] === Utils\Constants::BLOCK_POSTS_LIST ||
                $block['attrs']['namespace'] === Utils\Constants::BLOCK_ACTIONS_LIST
            );
    }

    /**
     * Replace the Post Terms blocks with the Taxonomy Breadcrumb block.
     */
    private static function transform_block(array &$blocks, string $type): void
    {
        $inserted = false; // Flag to ensure insertion happens only once

        foreach ($blocks as $key => &$block) {
            if (
                isset($block['blockName']) &&
                isset($block['attrs']) &&
                isset($block['attrs']['term']) &&
                array_key_exists($key, $blocks)
            ) {
                unset($blocks[$key]);

                if (!$inserted) {
                    $new_block = [
                        'blockName' => Utils\Constants::P4_OTHER_BLOCKS['breadcrumb'],
                        'attrs' => [
                            'taxonomy' => 'category',
                            'post_type' => $type === Utils\Constants::BLOCK_POSTS_LIST
                                ? Utils\Constants::POST_TYPES_POST
                                : Utils\Constants::POST_TYPES_ACTION,
                        ],
                    ];
                    $blocks = array_merge(
                        array_slice($blocks, 0, $key),
                        [$new_block],
                        array_slice($blocks, $key)
                    );
                    $inserted = true;
                }
            }
            if (!isset($block['innerBlocks']) || !is_array($block['innerBlocks'])) {
                continue;
            }
            self::transform_block($block['innerBlocks'], $type);
        }
    }
}
