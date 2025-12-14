<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Add "Load more" button and functionality to the Actions List blocks.
 */
class M057ActionsListBlockLoadMore extends MigrationScript
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
     * Check whether a block is an Actions List block without a "Load more" button.
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

        // Check if the block has a "Load more" button already.
        $load_more = array_find($block['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::BLOCK_BUTTONS &&
                $innerBlock['attrs']['className'] === 'load-more-button-container';
        });

        return empty($load_more);
    }

    /**
     * Add "load more" functionality, which includes updating the amount of posts per page
     * if the block uses the grid layout.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array &$block): array
    {
        // Create the "load more" button.
        $load_more = Utils\Functions::create_block_buttons(
            [
                'className' => 'load-more-actions-container',
                'layout' => ['type' => 'flex', 'justifyContent' => 'center'],
            ],
            [
                Utils\Functions::create_block_single_button(
                    ['className' => 'is-style-secondary', 'tagName' => 'button'],
                    __('Load more', 'planet4-master-theme'),
                ),
            ],
        );

        // Add it to the inner blocks.
        $block['innerBlocks'] = array_merge($block['innerBlocks'], [$load_more]);

        // Update the inner content accordingly.
        // IMPORTANT: DO NOT MODIFY THIS FORMAT!
        // phpcs:disable Generic.Files.LineLength.MaxExceeded
        $block['innerContent'] = array (
            0 => '
        <div class="wp-block-query actions-list p4-query-loop is-custom-layout-actions-list p4-query-loop is-custom-layout-grid">',
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
            8 => '
        ',
            9 => null,
            10 => '
        ',
            11 => null,
            12 => '</div>
        ',
        );

        // Change "perPage" query attribute to 24 if layout is grid.
        if (str_contains($block['attrs']['className'], 'is-custom-layout-grid')) {
            $block['attrs']['query']['perPage'] = 24;
        }

        return $block;
    }
}
