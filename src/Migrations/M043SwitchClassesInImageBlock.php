<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove unnecessary attributes in the core image block that produce errors.
 */
class M043SwitchClassesInImageBlock extends MigrationScript
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
            Utils\Constants::BLOCK_IMAGE,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a core image block.
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
        return $block['blockName'] === Utils\Constants::BLOCK_IMAGE;
    }

    /**
     * Remove unnecessary attributes.
     *
     * @param array $block - The image block.
     * @return array - The adjusted block.
     */
    private static function transform_block(array &$block): array
    {
        if (isset($block['attrs']['className']) && str_contains($block['attrs']['className'], 'is-style-rounded-')) {
            $to_remove =
            [
                'width:90px;height:90px',
                'width:180px;height:180px',
                'width="90"',
                'height="90"',
            ];
            $to_replace = [
                '',
                '',
                '',
                '',
            ];
            $html = str_replace($to_remove, $to_replace, $block['innerHTML']);

            $block['innerHTML'] = $html;
            $block['innerContent'][0] = $html;

            if (isset($block['attrs']['width'])) {
                unset($block['attrs']['width']);
            }
            if (isset($block['attrs']['height'])) {
                unset($block['attrs']['height']);
            }
        }
        return $block;
    }
}
