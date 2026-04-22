<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Fix `core/post-date` blocks inside Posts List blocks affected by a WordPress 6.9 regression.
 *
 * The editor's useEffect sets `datetime` to today's date on `core/post-date` blocks when the
 * attribute is undefined. When the page is saved, this value is persisted and the PHP renderer
 * uses it for every post in the loop, showing today's date instead of each post's actual
 * publish date.
 *
 * This migration removes the stale `datetime` attribute and adds the `core/post-data` block
 * binding so the editor reads the correct date from post context and won't re-write a stale
 * value on subsequent saves.
 */
class M063RemoveStaleDatetimeFromPostsListBlock extends MigrationScript
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
            $record
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a Posts List block that has a `core/post-date` needing a fix.
     * A fix is needed when the block either has a stale `datetime` attribute or is missing
     * the `core/post-data` binding that prevents the editor from re-writing a stale value.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        if (!is_array($block) || !isset($block['blockName']) || !isset($block['attrs']['namespace'])) {
            return false;
        }

        if (
            $block['blockName'] !== Utils\Constants::BLOCK_QUERY ||
            $block['attrs']['namespace'] !== Utils\Constants::BLOCK_POSTS_LIST
        ) {
            return false;
        }

        return self::has_post_date_needing_fix($block['innerBlocks'] ?? []);
    }

    /**
     * Recursively check whether any `core/post-date` block needs fixing.
     *
     * @param array $blocks - Array of block data arrays.
     */
    private static function has_post_date_needing_fix(array $blocks): bool
    {
        foreach ($blocks as $block) {
            if (isset($block['blockName']) && $block['blockName'] === Utils\Constants::BLOCK_DATE) {
                if (
                    isset($block['attrs']['datetime']) ||
                    !isset($block['attrs']['metadata']['bindings']['datetime'])
                ) {
                    return true;
                }
            }

            if (!empty($block['innerBlocks']) && self::has_post_date_needing_fix($block['innerBlocks'])) {
                return true;
            }
        }

        return false;
    }

    /**
     * Remove the stale `datetime` attribute and add the `core/post-data` binding to all
     * nested `core/post-date` blocks so the editor reads the correct date from post context.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        $block['innerBlocks'] = self::fix_post_date_blocks($block['innerBlocks'] ?? []);

        return $block;
    }

    /**
     * Recursively fix `core/post-date` blocks.
     *
     * @param array $blocks - Array of block data arrays.
     * @return array - The updated blocks.
     */
    private static function fix_post_date_blocks(array $blocks): array
    {
        foreach ($blocks as &$block) {
            if (isset($block['blockName']) && $block['blockName'] === Utils\Constants::BLOCK_DATE) {
                if (isset($block['attrs']['datetime'])) {
                    unset($block['attrs']['datetime']);
                }

                if (!isset($block['attrs']['metadata']['bindings']['datetime'])) {
                    $block['attrs']['metadata']['bindings']['datetime'] = [
                        'source' => 'core/post-data',
                        'args' => ['field' => 'date'],
                    ];
                }
            }

            if (empty($block['innerBlocks'])) {
                continue;
            }

            $block['innerBlocks'] = self::fix_post_date_blocks($block['innerBlocks']);
        }

        return $blocks;
    }
}
