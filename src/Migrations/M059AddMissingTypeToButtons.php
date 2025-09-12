<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Add missing "type" attribute to single buttons when tag name is "button".
 * This is probably happening for carousel arrows and the "load more" button,
 * in Posts/Actions List blocks.
 */
class M059AddMissingTypeToButtons extends MigrationScript
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
     * Check whether a block is a Posts/Actions List block.
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
     * Update the buttons of Posts/Actions List blocks if needed.
     *
     * @param array $block - The current posts/actions list block.
     * @return array - array of blocks.
     */
    private static function transform_block(array $block): array
    {
        self::update_buttons($block['innerBlocks']);

        return $block;
    }

     /**
     * Update Posts/Actions List block buttons.
     * These could be the carousel arrows or the "load more" button.
     *
     * @param array $blocks - array of blocks.
     */
    private static function update_buttons(array &$blocks): void
    {
        foreach ($blocks as &$block) {
            if (isset($block['blockName']) && $block['blockName'] === Utils\Constants::BLOCK_BUTTONS) {
                if (!empty($block['innerBlocks'])) {
                    foreach ($block['innerBlocks'] as &$innerBlock) {
                        // Check if the block is a button, with the tagName "button" but no set "type".
                        if (
                            !isset($innerBlock['blockName']) ||
                            $innerBlock['blockName'] !== Utils\Constants::BLOCK_SINGLE_BUTTON ||
                            !isset($innerBlock['attrs']['tagName']) ||
                            $innerBlock['attrs']['tagName'] !== 'button' ||
                            !isset($innerBlock['innerHTML']) ||
                            str_contains($innerBlock['innerHTML'], 'type="button"')
                        ) {
                            continue;
                        }

                        $innerBlock['attrs']['type'] = 'button';

                        // If it's an 'a' tag, replace it with a 'button' tag.
                        $new_button_html = $innerBlock['innerHTML'];
                        if (str_contains($new_button_html, '<a')) {
                            $new_button_html = str_replace(
                                '<a',
                                '<button',
                                $new_button_html,
                            );
                            $new_button_html = str_replace(
                                '</a>',
                                '</button>',
                                $new_button_html,
                            );
                        }

                        // Add 'type="button"' to markup.
                        $new_button_html = str_replace(
                            '<button',
                            '<button type="button"',
                            $new_button_html,
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

            self::update_buttons($block['innerBlocks']);
        }
    }
}
