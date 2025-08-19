<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Update Quick Links Block with a new ClassName
 */
class M056UpdateQuickLinksClassName extends MigrationScript
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
            Utils\Constants::BLOCK_TEMPLATE_QUICK_LINKS,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether the Quick Links blocks is into a page.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
       // Check if the block is an array, has a 'blockName' key.
        if (!is_array($block) || !isset($block['blockName']) && !empty($block['innerBlocks'])) {
            return false;
        }

        // Check if the block is a Quick Links block.
        return
            $block['blockName'] === Utils\Constants::BLOCK_TEMPLATE_QUICK_LINKS
        ;
    }

    /**
     * Modify the Quick Links block to add the quick-links class name.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array &$block): array // Using the parameter as a reference to update it
    {
        self::update_class_name($block['innerBlocks'], 'core/columns');
        return $block;
    }

    /**
     * Update the class name of the Quick Links block.
     *
     * @param array $innerBlocks - The inner-block of the Quick Links block.
     * @param string $blockName - The name of the block to update.
     * @param array $path - The path of the block to update.
     * @return array|null - The updated inner blocks.
     */
    private static function update_class_name(array &$innerBlocks, string $blockName, array $path = []): ?array
    {
        foreach ($innerBlocks as $index => &$innerBlock) {
            $currentPath = array_merge($path, [$index]);
            if ($innerBlock['blockName'] === $blockName) {
                if (!str_contains($innerBlock['attrs']['className'], "quick-links")) {
                    $innerBlock['attrs']['className'] = ($innerBlock['attrs']['className'] ?? '') . ' quick-links';

                    // Update inner content and replace className by adding the one related to Quick Links
                    foreach ($innerBlock['innerContent'] as &$innerContent) {
                        if (!is_string($innerContent) || !str_contains($innerContent, 'wp-block-columns')) {
                            continue;
                        }

                        $innerContent = preg_replace(
                            '/class="([^"]*)"/',
                            'class="$1 quick-links"',
                            $innerContent
                        );
                    }
                }
                return $innerBlocks;
            }

            if (empty($innerBlock['innerBlocks'])) {
                continue;
            }

            $found = self::update_class_name($innerBlock['innerBlocks'], $blockName, $currentPath);
            if (!empty($found)) {
                return $found;
            }
        }
        return null;
    }
}
