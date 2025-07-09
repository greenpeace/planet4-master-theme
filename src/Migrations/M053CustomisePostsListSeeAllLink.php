<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Customise "see all" link in Posts List blocks.
 */
class M053CustomisePostsListSeeAllLink extends MigrationScript
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
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a Posts List block with taxonomy filters.
     *
     * @param array $block - A block data array.
     */
    private static function check_is_valid_block(array $block): bool
    {
        // Check if the News & Stories page is defined in the settings.
        $news_page_id = (int) get_option('page_for_posts');
        if (!$news_page_id) {
            return false;
        }

        // Check if the block is an array, has a 'blockName' key, and has a namespace.
        if (!is_array($block) || !isset($block['blockName']) || !isset($block['attrs']['namespace'])) {
            return false;
        }

        // Check if the block is a Posts List block.
        if (
            $block['blockName'] !== Utils\Constants::BLOCK_QUERY ||
            $block['attrs']['namespace'] !== Utils\Constants::BLOCK_POSTS_LIST
        ) {
            return false;
        }

        // Check if the block has a "see all" link, editors could have removed it.
        $see_all_link = array_find($block['innerBlocks'], function ($innerBlock) {
            return $innerBlock['blockName'] === Utils\Constants::BLOCK_NAV_LINK &&
                $innerBlock['attrs']['className'] === 'see-all-link';
        });
        if (!$see_all_link) {
            return false;
        }

        // Check if the block has taxonomy filters.
        return isset($block['attrs']['query']['taxQuery']);
    }

    /**
     * Update "see all" link to add the taxonomy filters.
     *
     * @param array $block - A block data array.
     * @return array - The transformed block.
     */
    private static function transform_block(array $block): array
    {
        // Get the News & Stories page url.
        $news_page_id = (int) get_option('page_for_posts');
        $news_page_url = get_permalink($news_page_id);

        // Create the new "see all" URL based on taxonomy filters.
        $new_see_all_url = $news_page_url . '?';
        $filters = $block['attrs']['query']['taxQuery'];
        $tag_id = isset($filters['post_tag']) ? (int) $filters['post_tag'][0] : null;
        $category_id = isset($filters['category']) ? (int) $filters['category'][0] : null;
        $post_type_id = isset($filters['p4-page-type']) ? (int) $filters['p4-page-type'][0] : null;

        // Add tag filter
        if ($tag_id) {
            $tag = get_term_by('id', $tag_id, 'post_tag');
            $new_see_all_url .= 'tag=' . $tag->slug . '&';
        }
        // Add category filter
        if ($category_id) {
            $category = get_category_by_id($category_id);
            $new_see_all_url .= 'category=' . $category->slug . '&';
        }
        // Add post type filter
        if ($post_type_id) {
            $post_type = get_term_by('id', $post_type_id, 'p4-page-type');
            $new_see_all_url .= 'post-type=' . $post_type->slug;
        }

        // Update the "see all" link in inner blocks.
        foreach ($block['innerBlocks'] as &$innerBlock) {
            if (
                $innerBlock['blockName'] !== Utils\Constants::BLOCK_NAV_LINK ||
                $innerBlock['attrs']['className'] !== 'see-all-link'
            ) {
                continue;
            }
            // We remove the ending "&" character if needed.
            $innerBlock['attrs']['url'] = rtrim($new_see_all_url, '&');
        }

        return $block;
    }
}
