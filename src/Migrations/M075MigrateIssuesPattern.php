<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Patterns.
 */
class M075MigrateIssuesPattern extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $check_is_valid_block = function ($block) {
            return self::check_is_valid_block($block);
        };

        $transform_block = function ($block) {
            return self::transform_block($block);
        };

        Utils\Functions::execute_pattern_migration(
            Utils\Constants::BLOCK_PATTERN_ISSUES,
            $check_is_valid_block,
            $transform_block,
        );
    }

    /**
     * Check whether a pattern is an Issues block pattern.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        if (!is_array($block) || !isset($block['blockName'])) {
            return false;
        }

        return $block['blockName'] === Utils\Constants::BLOCK_TEMPLATE_ISSUES;
    }

    /**
     * Transform the blocks.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        unset($block['attrs']['current_post_id']);

        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            $block['innerBlocks'] = Utils\Functions::transform_heading_levels(
                $block['innerBlocks'],
                null,
                3,
                __('Enter text', 'planet4-master-theme-backend'),
            );

            // Render the items to a list of links i.e. <ul><li><a>.
            $block['innerBlocks'] = Utils\Functions::mark_pattern_lists(
                $block['innerBlocks'],
                fn (array $inner_block) => $inner_block['blockName'] === Utils\Constants::BLOCK_GROUP &&
                    str_contains($inner_block['attrs']['className'] ?? '', 'is-style-space-evenly'),
            );
        }

        return $block;
    }
}
