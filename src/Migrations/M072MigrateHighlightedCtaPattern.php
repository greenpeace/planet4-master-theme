<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Patterns.
 */
class M072MigrateHighlightedCtaPattern extends MigrationScript
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
            Utils\Constants::BLOCK_PATTERN_HIGHLIGHTED_CTA,
            $check_is_valid_block,
            $transform_block,
        );
    }

    /**
     * Check whether a pattern is a Highlighted CTA block pattern.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        if (!is_array($block) || !isset($block['blockName'])) {
            return false;
        }

        return $block['blockName'] === Utils\Constants::BLOCK_TEMPLATE_HIGHLIGHTED_CTA;
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
                2,
                null,
                null,
                __('Enter text', 'planet4-master-theme-backend'),
            );
        }

        return $block;
    }
}
