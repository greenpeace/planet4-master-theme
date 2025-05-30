<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Add tags back in Posts List block (visible in list layout only).
 */
class M050AddTagsBackInPostsListBlock extends MigrationScript
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
     * Check whether a block is a Query Loop block.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        // Check if the block is an array, has a 'blockName' key, and has a namespace.
        if (!is_array($block) || !isset($block['blockName']) || !isset($block['attrs']['namespace'])) {
            return false;
        }

        // Check if the block is a Posts List block.
        return $block['blockName'] === Utils\Constants::BLOCK_QUERY &&
            $block['attrs']['namespace'] === Utils\Constants::BLOCK_POSTS_LIST;
    }

    /**
     * Add tags back to the Posts List block, after the taxonomy breadcrumb block.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        self::insert_tags($block['innerBlocks']);

        return $block;
    }

    /**
     * Insert tags after the P4 taxonomy breadcrumb block.
     *
     * @param array $blocks - array of blocks.
     */
    private static function insert_tags(array &$blocks): void
    {
        foreach ($blocks as $key => &$block) {
            if (
                isset($block['blockName']) &&
                $block['blockName'] === Utils\Constants::BLOCK_GROUP &&
                $block['innerBlocks'][0]['blockName'] === Utils\Constants::P4_OTHER_BLOCKS['breadcrumb']
            ) {
                $new_block = Utils\Functions::create_new_block(
                    Utils\Constants::BLOCK_TERMS,
                    ['term' => 'post_tag', 'separator' => ' '],
                );
                $tax_block = Utils\Functions::create_new_block(
                    Utils\Constants::P4_OTHER_BLOCKS['breadcrumb'],
                    ['taxonomy' => 'category', 'post_type' => Utils\Constants::POST_TYPES_POST],
                );

                $block['innerBlocks'] = array_merge($block['innerBlocks'], [$new_block]);

                // IMPORTANT: DO NOT MODIFY THIS FORMAT!
                $block['innerHTML'] =
                '<div class="wp-block-group">
                </div>';
                // IMPORTANT: DO NOT MODIFY THIS FORMAT!
                $block['innerContent'] = array (
                    0 => '
                <div class="wp-block-group">',
                    1 => null,
                    2 => '
                ',
                    3 => null,
                    4 => '
                ',
                    5 => null,
                    6 => '
                ',
                    7 => null,
                    8 => '</div>
                ',
                );
            }

            self::insert_tags($block['innerBlocks']);
        }
    }
}
