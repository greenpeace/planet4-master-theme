<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove links in the Actions List blocks, since we have an overlay they don't make sense.
 * For the featured image and the category.
 */
class M062RemoveActionsListImageLink extends MigrationScript
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
     * Check whether a block is an Actions List block with a featured image link.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        // Check if the block is an array, has a 'blockName' key and a namespace.
        if (!is_array($block) || !isset($block['blockName']) || !isset($block['attrs']['namespace'])) {
            return false;
        }

        // Check if the block is an Actions List block.
        if (
            $block['blockName'] !== Utils\Constants::BLOCK_QUERY ||
            $block['attrs']['namespace'] !== Utils\Constants::BLOCK_ACTIONS_LIST
        ) {
            return false;
        }

        // Check if the block's featured image exists and is a link.
        // First we find the post template block, in its inner blocks we can find the featured image.
        $post_template = array_find($block['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::BLOCK_POST_TEMPLATE;
        });

        if (!$post_template || !$post_template['innerBlocks']) {
            return false;
        }

        $featured_image = array_find($post_template['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::BLOCK_FEAT_IMAGE;
        });

        return $featured_image && isset($featured_image['attrs']['isLink'])
            && $featured_image['attrs']['isLink'] === true;
    }

    /**
     * Remove link in the Actions List featured image.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array &$block): array
    {
        self::remove_featured_image_link($block['innerBlocks']);

        return $block;
    }

    /**
     * Remove link from featured image.
     *
     * @param array $blocks - array of blocks.
     */
    private static function remove_featured_image_link(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (isset($block['blockName']) && $block['blockName'] === Utils\Constants::BLOCK_POST_TEMPLATE) {
                if (!empty($block['innerBlocks'])) {
                    foreach ($block['innerBlocks'] as &$innerBlock) {
                        if (
                            !isset($innerBlock['blockName']) ||
                            $innerBlock['blockName'] !== Utils\Constants::BLOCK_FEAT_IMAGE
                        ) {
                            continue;
                        }
                        unset($innerBlock['attrs']['isLink']);
                    }
                }
            }

            self::remove_featured_image_link($block['innerBlocks']);
        }
    }
}
