<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Campaign covers block to Planet4 columns block.
 */
class M033MigrateCampaignCoversToP4ColumnsBlock extends MigrationScript
{
    /**
     * Extract campaign covers block from page/posts and transform it into Planet4 columns block.
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
            Utils\Constants::BLOCK_COVERS,
            $check_is_valid_block,
            $transform_block,
        );
    }

    /**
     * Check whether a block is a Campaign Covers block.
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

        // Check if the block is a Cover block. If not, abort.
        if ($block['blockName'] !== Utils\Constants::BLOCK_COVERS) {
            return false;
        }

        // For older cover blocks where the cover type is empty,
        // the default content cover type is applied to those blocks.
        if (!isset($block['attrs']['cover_type'])) {
            return false;
        }

        // For old cover type(earlier it was numeric).
        if (is_numeric($block['attrs']['cover_type'])) {
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            $block['attrs']['cover_type'] = Utils\Constants::OLD_COVER_TYPES[ $block['attrs']['cover_type'] ];
        }

        // Skip non campaign cover style blocks.
        return $block['attrs']['cover_type'] === Utils\Constants::COVER_TYPE_CAMPAIGN;
    }

    /**
     * Transform a block attrs into columns block.
     *
     * @param array $block_attrs - A block attrs array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        $block_attrs['columns_block_style'] = 'image';
        $block_attrs['columns_title'] = $block['attrs']['title'] ?? '';
        $block_attrs['className'] = 'is-style-image';

        $block_attrs['columns_description'] = $block['attrs']['description'] ?
            str_replace("u003cbru003e", " - ", $block['attrs']['description']) : // Replace line breaks.
            '';

        if (isset($block['attrs']['tags'])) {
            // To keep the same order of columns, reverse the array.
            $block['attrs']['tags'] = array_reverse($block['attrs']['tags']);
            foreach ($block['attrs']['tags'] as $tag_id) {
                $tag = get_tag($tag_id);
                if (!$tag) {
                    continue;
                }

                // Prepare tags(columns) array data.
                $block_attrs['columns'][] = [
                    'attachment' => get_term_meta($tag_id, 'tag_attachment_id', true),
                    'title' => '#' . html_entity_decode($tag->name),
                    'cta_link' => get_tag_link($tag),
                ];
            }
        }
        return Utils\Functions::create_block_p4_columns($block_attrs);
    }
}
