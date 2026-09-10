<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Fix empty gallery block static content.
 */
class M068EmptyGalleryBlockStaticContent extends MigrationScript
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
            echo "check_is_valid_block called\n";
            return self::check_is_valid_block($block, Utils\Constants::BLOCK_GALLERY);
        };

        $transform_block = function ($block) {
            return self::transform_block($block);
        };

        Utils\Functions::execute_block_migration(
            Utils\Constants::BLOCK_GALLERY,
            $check_is_valid_block,
            $transform_block,
        );
    }

     /**
     * Check whether a block exists in page.
     *
     * @param array $block - A block data array.
     */
    public static function check_is_valid_block(array $block): bool
    {
        // Check if the block is valid.
        if (!is_array($block)) {
            return false;
        }

        // Check if the block has a 'blockName' key.
        if (!isset($block['blockName'])) {
            return false;
        }

        // Check if the block is the desired block. If not, abort.
        return $block['blockName'] === Utils\Constants::BLOCK_GALLERY;
    }

    /**
     * Empty the block's HTML and leaving its attributes un-touched.
     *
     * @param array $block - The gallery block.
     * @return array - The adjusted block.
     */
    private static function transform_block(array &$block): array
    {
        unset($block['attrs']['current_post_id']);

        $block['innerHTML'] = '';
        $block['innerContent'] = [];

        return $block;
    }
}
