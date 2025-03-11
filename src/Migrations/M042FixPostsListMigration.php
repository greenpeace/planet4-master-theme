<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Posts List blocks with errors to Posts List blocks updated.
 */
class M042FixPostsListMigration extends MigrationScript
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
     * Check whether a block is a Posts List block.
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

        // Check if the block is a Posts List block.
        return $block['blockName'] === Utils\Constants::BLOCK_QUERY;
    }

    /**
     * Create a new Query block based on attributes of the existing Posts List block.
     *
     * @param array $block - The current posts list block.
     * @return array - The new block.
     */
    private static function transform_block(array $block): array
    {
        self::update_posts_list_block($block['innerBlocks']);
        return $block;
    }

     /**
     * Find either the title or description for the posts list block.
     *
     * @param array $blocks - The block.
     */
    private static function update_posts_list_block(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (isset($block['blockName']) && $block['blockName'] === 'core/post-title') {
                if (!isset($block['attrs'])) {
                    $block['attrs'] = [];
                }
                $block['attrs']['isLink'] = true;
                $block['attrs']['level'] = 4;
            }

            if (isset($block['blockName']) && $block['blockName'] === 'core/post-template') {
                $core_column_block = &$block['innerBlocks'][0];
                $core_group_block = &$core_column_block['innerBlocks'][1];
                $core_post_terms_block = &$core_group_block['innerBlocks'][0]['innerBlocks'];

                if (
                    isset($core_post_terms_block[0]['attrs']['term']) &&
                    $core_post_terms_block[0]['attrs']['term'] === 'category'
                ) {
                    continue;
                }

                if (!isset($core_post_terms_block[0]['attrs'])) {
                    $core_post_terms_block[0]['attrs'] = [];
                }

                if (!isset($core_post_terms_block[1]['attrs'])) {
                    $core_post_terms_block[1]['attrs'] = [];
                }

                $core_post_terms_block[0]['attrs']['term'] = 'category';
                $core_post_terms_block[0]['attrs']['separator'] = ' | ';
                $core_post_terms_block[1]['attrs']['term'] = 'post_tag';
                $core_post_terms_block[1]['attrs']['separator'] = ' ';
            }

            if (!isset($block['innerBlocks']) || !is_array($block['innerBlocks'])) {
                continue;
            }

            self::update_posts_list_block($block['innerBlocks']);
        }
    }
}
