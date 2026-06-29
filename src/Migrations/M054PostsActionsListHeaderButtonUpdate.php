<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Posts List blocks with errors to Posts List blocks fix.
 */
class M054PostsActionsListHeaderButtonUpdate extends MigrationScript
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
        // Check if the block is valid.
        if (!is_array($block)) {
            return false;
        }

        // Check if the block has a 'blockName' key.
        if (!isset($block['blockName'])) {
            return false;
        }

        // Check if the block is a Posts/Actions List block.
        return isset($block['blockName'], $block['attrs']['namespace']) &&
            $block['blockName'] === Utils\Constants::BLOCK_QUERY &&
            in_array($block['attrs']['namespace'], [
                Utils\Constants::BLOCK_POSTS_LIST,
                Utils\Constants::BLOCK_ACTIONS_LIST,
            ], true);
    }

    /**
     * Update the post title and buttons of Posts/Actions List blocks
     *
     * @param array $block - The current posts/actions list block.
     * @return array - array of blocks.
     */
    private static function transform_block(array $block): array
    {
        self::update_posts_list_block_title($block['innerBlocks']);
        self::update_posts_list_block_buttons($block['innerBlocks']);

        return $block;
    }

    /**
     * Update post titles.
     *
     * @param array $blocks - array of blocks.
     */
    private static function update_posts_list_block_title(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (isset($block['blockName']) && $block['blockName'] === Utils\Constants::BLOCK_TITLE) {
                if (!isset($block['attrs'])) {
                    $block['attrs'] = [];
                }
                $block['attrs']['isLink'] = true;
                $block['attrs']['level'] = 3;
            }

            self::update_posts_list_block_title($block['innerBlocks']);
        }
    }

     /**
     * Update Posts/Actions List block carousel buttons.
     *
     * @param array $blocks - array of blocks.
     */
    private static function update_posts_list_block_buttons(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (isset($block['blockName']) && $block['blockName'] === Utils\Constants::BLOCK_BUTTONS) {
                if (!empty($block['innerBlocks'])) {
                    foreach ($block['innerBlocks'] as &$innerBlock) {
                        if (
                            !isset($innerBlock['blockName']) ||
                            $innerBlock['blockName'] !== Utils\Constants::BLOCK_SINGLE_BUTTON
                        ) {
                            continue;
                        }

                        // Only change tagName if it's not already "button"
                        if (
                            !isset($innerBlock['attrs']['tagName']) ||
                            $innerBlock['attrs']['tagName'] !== 'button'
                        ) {
                            $innerBlock['attrs']['tagName'] = 'button';

                            // Remove href if it exists
                            if (isset($innerBlock['attrs']['href'])) {
                                unset($innerBlock['attrs']['href']);
                            }
                        }

                        if (!isset($innerBlock['innerHTML'])) {
                            continue;
                        }

                        $new_button_html = preg_replace(
                            ['/<a\b([^>]*)>/', '/<\/a>/'],
                            ['<button$1>', '</button>'],
                            $innerBlock['innerHTML']
                        );

                        $innerBlock['innerHTML'] = $new_button_html;

                        if (!isset($innerBlock['innerContent'][0])) {
                            continue;
                        }

                        $innerBlock['innerContent'][0] = $new_button_html;
                    }
                }
            }


            if (!isset($block['innerBlocks']) || !is_array($block['innerBlocks'])) {
                continue;
            }

            self::update_posts_list_block_buttons($block['innerBlocks']);
        }
    }
}
