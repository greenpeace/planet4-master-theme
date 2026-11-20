<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Patterns.
 */
class M073MigrateRealityCheckPattern extends MigrationScript
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
            Utils\Constants::BLOCK_PATTERN_REALITY_CHECK,
            $check_is_valid_block,
            $transform_block,
        );
    }

    /**
     * Check whether a pattern is a Reality Check block pattern.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        if (!is_array($block) || !isset($block['blockName'])) {
            return false;
        }

        return $block['blockName'] === Utils\Constants::BLOCK_TEMPLATE_REALITY_CHECK;
    }

    /**
     * Transform the blocks.
     *
     * Add a visually hidden section heading at the start of the pattern,
     * the same as we added by the current pattern template.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        unset($block['attrs']['current_post_id']);

        $inner_blocks = is_array($block['innerBlocks'] ?? null) ? $block['innerBlocks'] : [];

        if (self::has_visually_hidden_heading($inner_blocks)) {
            return $block;
        }

        $heading = Utils\Functions::create_block_heading(
            ['className' => 'visually-hidden'],
            esc_html__('Reality check', 'planet4-master-theme-backend'),
        );

        array_unshift($inner_blocks, $heading);
        $block['innerBlocks'] = $inner_blocks;

        $inner_content = is_array($block['innerContent'] ?? null) ? $block['innerContent'] : [];
        $first_placeholder = array_search(null, $inner_content, true);

        if ($first_placeholder === false) {
            $inner_content[] = null;
        } else {
            array_splice($inner_content, $first_placeholder, 0, [null, "\n\n"]);
        }

        $block['innerContent'] = $inner_content;

        return $block;
    }

    /**
     * Check when the pattern already has a visually hidden heading.
     *
     * @param array $blocks - The inner blocks of the pattern.
     */
    private static function has_visually_hidden_heading(array $blocks): bool
    {
        foreach ($blocks as $inner_block) {
            if (($inner_block['blockName'] ?? null) !== Utils\Constants::BLOCK_HEADING) {
                continue;
            }

            $classes = preg_split('/\s+/', trim($inner_block['attrs']['className'] ?? ''));

            if (in_array('visually-hidden', $classes, true)) {
                return true;
            }
        }

        return false;
    }
}
