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
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        $visually_hidden_heading = [
            'blockName' => 'core/heading',
            'attrs' => [
                'level' => 2 ,
                'className' => 'visually-hidden',
            ],
            'innerHTML' => '<h2>Reality check</h2>',
            'innerContent' => ['<h2>Reality check</h2>'],
        ];

        $has_visually_hidden = false;
        foreach ($block['innerBlocks'] as $inner_block) {
            if (
                isset($inner_block['blockName']) &&
                $inner_block['blockName'] === 'core/heading' &&
                isset($inner_block['attrs']['className']) &&
                str_contains($inner_block['attrs']['className'], 'visually-hidden') // Checks if class is present
            ) {
                $has_visually_hidden = true;
                break;
            }
        }

        if (!$has_visually_hidden) {
            array_unshift($block['innerBlocks'], $visually_hidden_heading);

            if (count($block['innerContent']) > 0 && is_string($block['innerContent'][0])) {
                array_splice($block['innerContent'], 1, 0, [null]);
            } else {
                array_unshift($block['innerContent'], null);
            }
        }

        return $block;
    }
}
