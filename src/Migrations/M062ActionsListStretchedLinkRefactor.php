<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Refactor Actions List blocks to use the stretched link functionality.
 * This allows us to simplify the block and get rid of some JS code.
 * At the same time, we remove the links from the featured image and the category.
 */
class M062ActionsListStretchedLinkRefactor extends MigrationScript
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
     * Check whether a block is an Actions List block with one of the attributes that need to be updated.
     * Either the featured image or category is a link, or the block doesn't have a stretched link.
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

        // Find the post template block in the inner blocks, the featured image is one of its inner blocks.
        $post_template = array_find($block['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::BLOCK_POST_TEMPLATE;
        });

        if (!$post_template || !$post_template['innerBlocks']) {
            return false;
        }

        // Check if the featured image block is a link.
        $featured_image_link = array_find($post_template['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::BLOCK_FEAT_IMAGE &&
                isset($innerBlock['attrs']['isLink']) &&
                $innerBlock['attrs']['isLink'] === true;
        });

        // Check if there is a group block with no classname, that's the one that should become a stretched link.
        $no_stretched_link = array_find($post_template['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::BLOCK_GROUP &&
                !isset($innerBlock['attrs']['className']);
        });

        // Check if the category is a link, meaning it doesn't have the new 'isLink' attribute set to false.
        $category_link = array_find($no_stretched_link['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::P4_OTHER_BLOCKS['breadcrumb'] &&
                !isset($innerBlock['attrs']['isLink']);
        });

        return $featured_image_link || $no_stretched_link || $category_link;
    }

    /**
     * Make all the necessary changes.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array &$block): array
    {
        // Remove featured image link.
        self::remove_featured_image_link($block['innerBlocks']);

        // Add stretched link functionality to group.
        self::add_stretched_link($block['innerBlocks']);

        // Remove category link.
        self::remove_category_link($block['innerBlocks']);

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
            if (!self::is_valid_post_template_block($block)) {
                continue;
            }
            foreach ($block['innerBlocks'] as &$innerBlock) {
                if (
                    !isset($innerBlock['blockName']) ||
                    $innerBlock['blockName'] !== Utils\Constants::BLOCK_FEAT_IMAGE ||
                    !isset($innerBlock['attrs']['isLink'])
                ) {
                    continue;
                }
                unset($innerBlock['attrs']['isLink']);
            }
        }
    }

    /**
     * Make the group a stretched link, and remove category link.
     *
     * @param array $blocks - array of blocks.
     */
    private static function add_stretched_link(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (!self::is_valid_post_template_block($block)) {
                continue;
            }
            foreach ($block['innerBlocks'] as &$innerBlock) {
                if (
                    !isset($innerBlock['blockName']) ||
                    $innerBlock['blockName'] !== Utils\Constants::BLOCK_GROUP ||
                    isset($innerBlock['attrs']['className'])
                ) {
                    continue;
                }
                $innerBlock['attrs']['className'] = 'group-stretched-link';

                // IMPORTANT: DO NOT MODIFY THIS FORMAT!
                $innerBlock['innerHTML'] =
                '<div class="wp-block-group group-stretched-link">



                </div>
                ';

                // IMPORTANT: DO NOT MODIFY THIS FORMAT!
                $innerBlock['innerContent'] = array(
                    0 => '
                <div class="wp-block-group group-stretched-link">',
                    1 => null,
                    2 => '
                ',
                    3 => null,
                    4 => '
                ',
                    5 => null,
                    6 => '</div>
                ',
                );
            }
        }
    }

    /**
     * Remove category link.
     *
     * @param array $blocks - array of blocks.
     */
    private static function remove_category_link(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (!self::is_valid_post_template_block($block)) {
                continue;
            }
            foreach ($block['innerBlocks'] as &$innerBlock) {
                if (
                    !isset($innerBlock['blockName']) ||
                    $innerBlock['blockName'] !== Utils\Constants::BLOCK_GROUP ||
                    !isset($innerBlock['attrs']['className']) ||
                    $innerBlock['attrs']['className'] !== 'group-stretched-link'
                ) {
                    continue;
                }
                foreach ($innerBlock['innerBlocks'] as &$inner_innerBlock) {
                    if (
                        !isset($inner_innerBlock['blockName']) ||
                        $inner_innerBlock['blockName'] !== Utils\Constants::P4_OTHER_BLOCKS['breadcrumb'] ||
                        isset($inner_innerBlock['attrs']['isLink'])
                    ) {
                        continue;
                    }
                    $inner_innerBlock['attrs']['isLink'] = false;
                }
            }
        }
    }

    /**
     * Check that the given block is a valid Post Template.
     *
     * @param array $block - array of attributes.
     */
    private static function is_valid_post_template_block(array $block): bool
    {
        return isset($block['blockName']) &&
            $block['blockName'] === Utils\Constants::BLOCK_POST_TEMPLATE &&
            !empty($block['innerBlocks']);
    }
}
