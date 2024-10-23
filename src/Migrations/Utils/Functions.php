<?php

namespace P4\MasterTheme\Migrations\Utils;

use P4\MasterTheme\BlockReportSearch\BlockSearch;
use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters;

/**
 * Utility functions for the migration scripts.
 */
class Functions
{
    /**
     * Get all the posts using a specific type of block.
     *
     * @param string $block_name - The name of the block to be searched.
     * @param array $post_types - The list of post types to look for.
     * @param array $post_status - The list of post status to look for.
     * @return mixed - The posts using a type of block or null if no posts are found.
     */
    //phpcs:ignore Generic.Files.LineLength.MaxExceeded
    public static function get_posts_using_specific_block(string $block_name, array $post_types, array $post_status): mixed
    {
        $search = new BlockSearch();

        $post_ids = $search->get_posts(
            ( new Parameters() )->with_name($block_name)->with_post_status($post_status)
        );

        if (empty($post_ids)) {
            return null;
        }

        $args = [
            'include' => $post_ids,
            'post_type' => $post_types,
            'post_status' => 'any',
        ];

        $posts = get_posts($args) ?? [];

        if (empty($posts)) {
            return null;
        }

        return $posts;
    }

    /**
     * Create an array with the minimum data necessary to set a new Gutenberg block.
     *
     * @param string $name - The block name.
     * @param array $attrs - The block attributes.
     * @return array - The new block.
     */
    public static function set_new_block(string $name, array $attrs): array
    {
        $block = [];
        $block['blockName'] = $name;
        $block['attrs'] = $attrs;
        return $block;
    }

    /**
     * Create an array with the empty inner attributes for a block.
     * The inner attributes are innerHTML, innerBlocks, and innerContent.
     *
     * @param array $block - The block data.
     * @return mixed - The block data with the extra attributes.
     */
    public static function set_empty_inner_attrs(array $block): array
    {
        $block['innerHTML'] = '';
        $block['innerBlocks'] = [];
        $block['innerContent'] = [];
        return $block;
    }
}
