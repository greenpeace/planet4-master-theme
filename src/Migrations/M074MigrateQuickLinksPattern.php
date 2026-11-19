<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Patterns.
 */
class M074MigrateQuickLinksPattern extends MigrationScript
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
            Utils\Constants::BLOCK_PATTERN_QUICK_LINKS,
            $check_is_valid_block,
            $transform_block,
        );
    }

    /**
     * Check whether a pattern is a Quick Links block pattern.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        if (!is_array($block) || !isset($block['blockName'])) {
            return false;
        }

        return $block['blockName'] === Utils\Constants::BLOCK_TEMPLATE_QUICK_LINKS;
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
            $block['innerBlocks'] = self::transform_headings($block['innerBlocks']);

            // Render the items as a list of links (<ul><li><a>).
            $block['innerBlocks'] = Utils\Functions::mark_pattern_lists(
                $block['innerBlocks'],
                fn (array $inner_block) => $inner_block['blockName'] === Utils\Constants::BLOCK_COLUMNS,
            );
        }

        return $block;
    }

    /**
     * Fix the heading levels of the pattern.
     *
     * - Block title <h4> to <h2>.
     * - Item titles <h5> to <h3> which the same as the current pattern template.
     *
     * @param array $blocks - A list of block data arrays.
     * @param bool $in_columns - Whether the blocks are inside the items columns.
     * @return array - The transformed blocks.
     */
    private static function transform_headings(array $blocks, bool $in_columns = false): array
    {
        foreach ($blocks as &$block) {
            $block_name = $block['blockName'] ?? null;

            if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
                $block['innerBlocks'] = self::transform_headings(
                    $block['innerBlocks'],
                    $in_columns || $block_name === 'core/columns'
                );
            }

            if ($block_name !== 'core/heading') {
                continue;
            }

            $level = $block['attrs']['level'] ?? 2;

            if (!$in_columns && $level === 4) {
                Utils\Functions::replace_heading_level($block, 4, 2);

                // Level 2 is the default for core/heading, so the editor doesn't stores it.
                unset($block['attrs']['level']);
                continue;
            }
        }

        unset($block);

        return $blocks;
    }
}
