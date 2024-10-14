<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Block_Parser;

/**
 * Migrate Campaign covers block to Planet4 columns block.
 */
class M032MigrateCampaignCoversToP4ColumnsBlock extends MigrationScript
{
    /**
     * Extract campaign covers block from page/posts and transform it into Planet4 columns block.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        try {
            // Get the list of posts using Covers block.
            $posts = Utils\Functions::get_posts_using_specific_block(
                Utils\Constants::BLOCK_COVERS,
                Utils\Constants::ALL_POST_TYPES
            );

            // If there are no posts, abort.
            if (!$posts) {
                return;
            }

            $parser = new WP_Block_Parser();

            echo "Campaign Covers block migration in progress...\n"; // phpcs:ignore

            foreach ($posts as $post) {
                if (empty($post->post_content)) {
                    continue;
                }

                $current_post_id = $post->ID; // Store the current post ID

                // Go through the post blocks to find the planet4-blocks/covers one.
                $blocks = $parser->parse($post->post_content);

                foreach ($blocks as &$block) {
                    // Skip non cover block.
                    if (!isset($block['blockName']) || $block['blockName'] !== Utils\Constants::BLOCK_COVERS) {
                        continue;
                    }

                    // For older cover blocks where the cover type is empty,
                    // the default content cover type is applied to those blocks.
                    if (!isset($block['attrs']['cover_type'])) {
                        continue;
                    }

                    // For old cover type(earlier it was numeric).
                    if (is_numeric($block['attrs']['cover_type'])) {
                        // phpcs:ignore Generic.Files.LineLength.MaxExceeded
                        $block['attrs']['cover_type'] = Utils\Constants::OLD_COVER_TYPES[ $block['attrs']['cover_type'] ];
                    }

                    // Skip non campaign cover style blocks.
                    if ($block['attrs']['cover_type'] !== Utils\Constants::COVER_TYPE_CAMPAIGN) {
                        continue;
                    }

                    $block = self::transform_block($block);
                }

                // Unset the reference to prevent potential issues.
                unset($block);

                // Serialize the blocks content.
                $new_content = serialize_blocks($blocks);

                if ($post->post_content === $new_content) {
                    continue;
                }

                echo 'Migrating post ', $current_post_id, "\n"; // phpcs:ignore

                $post_update = array(
                    'ID' => $current_post_id,
                    'post_content' => $new_content,
                );

                // Update the post with the replaced blocks.
                $result = wp_update_post(wp_slash($post_update));

                if ($result === 0) {
                    throw new \Exception("There was an error trying to update the post #" . $current_post_id);
                }

                echo $result
                    ? "Migration successful\n"
                    : "Migration wasn't executed\n"; // phpcs:ignore
            }
        } catch (\Throwable $e) {
            // Catch any exceptions and display the post ID if available
            echo "Migration wasn't executed for post ID: ", $current_post_id ?? 'unknown', "\n";
            echo $e->getMessage(), "\n";
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Transform a block attrs into columns block.
     *
     * @param array $block_attrs - A block attrs array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        // Gathering the data we want from this block.
        $block_attrs = self::get_columns_block_attrs($block['attrs']);

        $p4_columns_block['blockName'] = Utils\Constants::BLOCK_COLUMNS;
        $p4_columns_block['attrs'] = $block_attrs;
        $p4_columns_block['innerBlocks'] = [];
        $p4_columns_block['innerHTML'] = '';
        $p4_columns_block['innerContent'] = [];

        return $p4_columns_block;
    }

    /**
     * Get the p4 columns block attrs.
     *
     * @param array $block - A parsed cover block.
     * @return array - A P4 columns block attrs.
     */
    private static function get_columns_block_attrs(array $block): array
    {
        $block_attrs['columns_block_style'] = 'image';
        $block_attrs['columns_title'] = $block['title'] ?? '';
        $block_attrs['columns_description'] = $block['description'] ?? '';

        if (isset($block['tags'])) {
            // To keep the same order of columns, reverse the array.
            $block['tags'] = array_reverse($block['tags']);
            foreach ($block['tags'] as $tag_id) {
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
        $block_attrs['className'] = 'is-style-image';

        return $block_attrs;
    }
}
