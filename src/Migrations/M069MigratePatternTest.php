<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate the Test Pattern.
 */
class M069MigratePatternTest extends MigrationScript
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
            Utils\Constants::PATTERN_TEST_LAYOUT,
            $check_is_valid_block,
            $transform_block,
        );
    }

    /**
     * Check whether a pattern is a Test pattern.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        if (!is_array($block) || !isset($block['blockName'])) {
            return false;
        }

        return $block['blockName'] === Utils\Constants::BLOCK_TEMPLATE_PATTERN_TEST;
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

        $replaced = false;
        if (!empty($block['innerBlocks']) && is_array($block['innerBlocks'])) {
            $block['innerBlocks'] = self::modify_heading($block['innerBlocks'], $replaced);
        }

        return $block;
    }

    private static function modify_heading(array $blocks, int &$replaced): array
    {
        foreach ($blocks as &$block) {
            if (isset($block['blockName']) && $block['blockName'] === 'core/heading') {
                $from_level = 1;
                $to_level = 3;
                if (isset($block['attrs']['level']) && $block['attrs']['level'] === $from_level) {
                    $block['attrs']['level'] = $to_level;
                    // phpcs:ignore Generic.Files.LineLength.MaxExceeded
                    $block['innerHTML'] = str_replace('<h' . $from_level, '<h' . $to_level, $block['innerHTML']);
                    // phpcs:ignore Generic.Files.LineLength.MaxExceeded
                    $block['innerHTML'] = str_replace('</h' . $from_level . '>', '</h' . $to_level . '>', $block['innerHTML']);
                    // phpcs:ignore Generic.Files.LineLength.MaxExceeded
                    $block['innerContent'][0] = str_replace('<h' . $from_level, '<h' . $to_level, $block['innerContent'][0]);
                    // phpcs:ignore Generic.Files.LineLength.MaxExceeded
                    $block['innerContent'][0] = str_replace('</h' . $from_level . '>', '</h' . $to_level . '>', $block['innerContent'][0]);
                    $replaced = true;
                }
            }

            if (empty($block['innerBlocks']) || $replaced) {
                continue;
            }

            $block['innerBlocks'] = self::modify_heading($block['innerBlocks'], $replaced);
        }

        return $blocks;
    }
}
