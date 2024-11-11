<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Covers block of type Content to Posts List blocks.
 */
class M034MigrateCoversContentBlockToPostsListBlock extends MigrationScript
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
            Utils\Constants::BLOCK_COVERS,
            $check_is_valid_block,
            $transform_block,
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Check whether a block is a Content Covers block.
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

        // Check if the Cover block type is Content. If not, abort.
        // Cover blocks of type content have as value of $block['attrs']['cover_type']
        // the following possibilities: "content", "1", NULL
        // https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/26b480a0954667a0813ca8c3a90377f2a1fbea1c/classes/blocks/class-covers.php#L35
        // https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/26b480a0954667a0813ca8c3a90377f2a1fbea1c/classes/blocks/class-covers.php#L191
        $type = $block['attrs']['cover_type'];
        $const = Utils\Constants::COVER_BLOCK_TYPES['content'];

        if (!isset($type) || $type === $const['name'] || $type === $const['number']) {
            return true;
        }

        return false;
    }

    /**
     * Get the attributes of a Covers block.
     *
     * @param array $existing_block - The current Covers block.
     * @return array - The attributes.
     */
    private static function get_posts_list_block_attrs(array $existing_block): array
    {
        $attrs = [];
        $attrs['title'] = $existing_block['attrs']['title'] ?? '';
        $attrs['description'] = $existing_block['attrs']['description'] ?? '';
        $attrs['cover_type'] = $existing_block['attrs']['cover_type'] ?? 'content';
        $attrs['layout'] = $existing_block['attrs']['layout'] ?? 'grid';
        $attrs['tags'] = $existing_block['attrs']['tags'] ?? [];
        $attrs['posts'] = $existing_block['attrs']['posts'] ?? [];
        $attrs['post_types'] = $existing_block['attrs']['post_types'] ?? [];
        return $attrs;
    }

    /**
     * Set all the attributes to create a new Query block.
     *
     * @param array $tags - The list of post tags.
     * @param array $posts_override - The list of posts to include in the query.
     * @param array $post_types - The list of terms of the "p4-page-type" taxonomy.
     * @param string $layout_type - The layout type (grid or carousel).
     * @return array - The attributes.
     */
    private static function set_query_block_attrs(array $tags, array $posts_override, array $post_types, string $layout_type): array
    {
        $query = [];
        $query['perPage'] = 4;
        $query['pages'] = 0;
        $query['offset'] = 0;
        $query['postType'] = Utils\Constants::POST_TYPES_POST;
        $query['order'] = 'desc';
        $query['orderBy'] = 'date';
        $query['author'] = '';
        $query['search'] = '';
        $query['exclude'] = [];
        $query['sticky'] = '';
        $query['inherit'] = false;
        $query['hasPassword'] = false;
        $query['postIn'] = $posts_override;

        if (!empty($tags)) {
            $query['taxQuery']['post_tag'] = $tags;
        }
        if (!empty($post_types)) {
            $query['taxQuery']['p4-page-type'] = $post_types;
        }

        $layout = [];
        $layout['type'] = $layout_type;
        $layout['columnCount'] = 4;

        $attrs = [];
        $attrs['queryId'] = 0;
        $attrs['query'] = $query;
        $attrs['namespace'] = Utils\Constants::BLOCK_POSTS_LIST;
        $attrs['layout'] = $layout;
        return $attrs;
    }

    /**
     * Create a new Query block based on attributes of a Covers block.
     *
     * @param array $block - The current Covers block.
     * @return array - The new block.
     */
    private static function transform_block(array $block): array
    {
        $existing_block_attrs = self::get_posts_list_block_attrs($block);

        $tags = $existing_block_attrs['tags'];
        $posts_override = $existing_block_attrs['posts'];
        $post_types = $existing_block_attrs['post_types'];
        $layout_type = $existing_block_attrs['layout'] === 'carousel' ? 'flex' : 'grid';
        $classname = $existing_block_attrs['layout'] === 'carousel' ? 'carousel' : 'grid';

        $inner_blocks = array (
            0 => self::get_head_group_block($existing_block_attrs['title']),
            1 => self::get_paragraph_block($existing_block_attrs['description']),
            2 => self::get_query_no_results_block(),
            3 => self::get_post_template(),
            4 => self::get_buttons_block(),
            5 => self::get_nav_links_block(),
        );

        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_QUERY;
        $block['attrs'] = self::set_query_block_attrs($tags, $posts_override, $post_types, $layout_type);
        $block['innerBlocks'] = $inner_blocks;
        $block['innerHTML'] = Utils\M034Helper::get_query_block_html_content($classname);
        $block['innerContent'] = Utils\M034Helper::get_query_block_inner_content($classname);
        return $block;
    }

    /**
     * Create and get a new heading block.
     *
     * @param string $title - The block title.
     * @return array - The new block.
     */
    private static function get_heading_block(string $title): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_HEADING;
        $block['attrs']['lock']['move'] = true;
        $block['innerBlocks'] = [];
        $block['innerHTML'] = Utils\M034Helper::get_heading_block_content($title);
        $block['innerContent'][0] = Utils\M034Helper::get_heading_block_content($title);
        return $block;
    }

    /**
     * Create and get a new button block.
     *
     * @param string $classname - The button class.
     * @param string $text - The button label.
     * @return array - The new block.
     */
    private static function get_button_block(string $classname, string $text): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_SINGLE_BUTTON;
        $block['attrs']['className'] = $classname;
        $block['innerBlocks'] = [];
        $block['innerHTML'] = Utils\M034Helper::get_button_block_content($classname, $text);
        $block['innerContent'][0] = Utils\M034Helper::get_button_block_content($classname, $text);
        return $block;
    }

    /**
     * Create and get a new buttons container block.
     *
     * @return array - The new block.
     */
    private static function get_buttons_block(): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_BUTTONS;
        $block['className'] = 'carousel-controls';
        $block['attrs']['lock']['move'] = true;
        $block['attrs']['layout']['type'] = 'flex';
        $block['attrs']['layout']['justifyContent'] = 'space-between';
        $block['attrs']['layout']['orientation'] = 'horizontal';
        $block['attrs']['layout']['flexWrap'] = 'nowrap';
        $block['innerBlocks'][0] = self::get_button_block('carousel-control-prev', 'Prev');
        $block['innerBlocks'][1] = self::get_button_block('carousel-control-next', 'Next');
        $block['innerHTML'] = Utils\M034Helper::BUTTONS_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::BUTTONS_BLOCK['content'];
        return $block;
    }

    /**
     * Create and get a new post template.
     *
     * @return array - The new template.
     */
    private static function get_post_template(): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_POST_TEMPLATE;
        $block['attrs']['lock']['move'] = true;
        $block['attrs']['lock']['remove'] = true;
        $block['innerBlocks'][0] = self::get_post_data_column_block();
        $block['innerHTML'] = Utils\M034Helper::POST_TEMPLATE['html'];
        $block['innerContent'] = Utils\M034Helper::POST_TEMPLATE['content'];
        return $block;
    }

    /**
     * Create and get a new navigation link block.
     *
     * @return array - The new block.
     */
    private static function get_nav_links_block(): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_NAV_LINK;
        $block['attrs']['label'] = 'See all stories';
        $block['attrs']['url'] = '/news-stories/';
        $block['attrs']['className'] = 'see-all-link';
        $block['innerBlocks'] = [];
        $block['innerHTML'] = '';
        $block['innerContent'][0] = '';
        return $block;
    }

    /**
     * Create and get a new query-no-results block.
     *
     * @return array - The new block.
     */
    private static function get_query_no_results_block(): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_QUERY_NO_RESULTS;
        $block['attrs'] = [];
        $block['innerBlocks'][0] = self::get_query_no_results_paragraph_block();
        $block['innerHTML'] = Utils\M034Helper::QUERY_NO_RESULTS_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::QUERY_NO_RESULTS_BLOCK['content'];
        return $block;
    }

    /**
     * Create and get a new paragraph block.
     *
     * @param string $description - The text for the paragraph.
     * @return array - The new block.
     */
    private static function get_paragraph_block(string $description): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_PARAGRAPH;
        $block['attrs']['placeholder'] = 'Enter description';
        $block['attrs']['lock']['move'] = true;
        $block['attrs']['style']['spacing']['margin']['top'] = '24px';
        $block['attrs']['style']['spacing']['margin']['bottom'] = '36px';
        $block['innerBlocks'] = [];
        $block['innerHTML'] = Utils\M034Helper::get_paragraph_block_content($description);
        $block['innerContent'][0] = Utils\M034Helper::get_paragraph_block_content($description);
        return $block;
    }

    /**
     * Create and get a new paragraph block for the query-no-results block.
     *
     * @return array - The new block.
     */
    private static function get_query_no_results_paragraph_block(): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_PARAGRAPH;
        $block['attrs'] = [];
        $block['innerBlocks'] = [];
        $block['innerHTML'] = Utils\M034Helper::QUERY_NO_RESULTS_PARAGRAPH_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::QUERY_NO_RESULTS_PARAGRAPH_BLOCK['content'];
        return $block;
    }

    /**
     * Create and get a new columns block.
     *
     * @return array - The new block.
     */
    private static function get_post_data_column_block(): array
    {
        $block = [];
        $feat_img_attrs = [];
        $feat_img_attrs['isLink'] = true;

        $block['blockName'] = Utils\Constants::BLOCK_COLUMNS;
        $block['attrs'] = [];
        $block['innerBlocks'][0] = Utils\Functions::set_new_block(Utils\Constants::BLOCK_FEAT_IMAGE, $feat_img_attrs);
        $block['innerBlocks'][1] = self::get_post_data_group_block();
        $block['innerHTML'] = Utils\M034Helper::COLUMN_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::COLUMN_BLOCK['content'];
        return $block;
    }

    /**
     * Create and get a new group block for the Covers head.
     *
     * @param string $title - The block title.
     * @return array - The new block.
     */
    private static function get_head_group_block(string $title): array
    {
        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_GROUP;
        $block['attrs']['layout']['type'] = 'flex';
        $block['attrs']['layout']['justifyContent'] = 'space-between';
        $block['innerBlocks'][0] = self::get_heading_block($title);
        $block['innerBlocks'][1] = self::get_nav_links_block();
        $block['innerHTML'] = Utils\M034Helper::HEAD_GROUP_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::HEAD_GROUP_BLOCK['content'];
        return $block;
    }

    /**
     * Create and get a new group block for the post taxonomy section.
     *
     * @return array - The new block.
     */
    private static function get_post_terms_group_block(): array
    {
        $post_tags_attrs = [];
        $post_tags_attrs['term'] = 'post_tag';
        $post_tags_attrs['separator'] = ' ';

        $post_cats_attrs = [];
        $post_cats_attrs['term'] = 'category';
        $post_cats_attrs['separator'] = ' | ';

        $block = [];
        $block['blockName'] = Utils\Constants::BLOCK_GROUP;
        $block['attrs']['layout']['type'] = 'flex';
        $block['innerBlocks'][0] = Utils\Functions::set_new_block(Utils\Constants::BLOCK_TERMS, $post_tags_attrs);
        $block['innerBlocks'][1] = Utils\Functions::set_new_block(Utils\Constants::BLOCK_TERMS, $post_cats_attrs);
        $block['innerHTML'] = Utils\M034Helper::POST_TERMS_GROUP_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::POST_TERMS_GROUP_BLOCK['content'];
        return $block;
    }

    /**
     * Create and get a new group block for the post metadata section.
     *
     * @return array - The new block.
     */
    private static function get_posts_list_meta_group_block(): array
    {
        $block = [];
        $author_attrs = [];
        $author_attrs['isLink'] = true;

        $block['blockName'] = Utils\Constants::BLOCK_GROUP;
        $block['attrs']['className'] = 'posts-list-meta';
        $block['innerBlocks'][0] = Utils\Functions::set_new_block(Utils\Constants::BLOCK_AUTHOR, $author_attrs);
        $block['innerBlocks'][1] = Utils\Functions::set_new_block(Utils\Constants::BLOCK_DATE, []);
        $block['innerHTML'] = Utils\M034Helper::POSTS_LIST_META_GROUP_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::POSTS_LIST_META_GROUP_BLOCK['content'];
        return $block;
    }

    /**
     * Create and get a new group block for the post data section.
     *
     * @return array - The new block.
     */
    private static function get_post_data_group_block(): array
    {
        $block = [];
        $post_title_attrs = [];
        $post_cats_attrs['isLink'] = true;

        $block['blockName'] = Utils\Constants::BLOCK_GROUP;
        $block['attrs'] = [];
        $block['innerBlocks'][0] = self::get_post_terms_group_block();
        $block['innerBlocks'][1] = Utils\Functions::set_new_block(Utils\Constants::BLOCK_TITLE, $post_title_attrs);
        $block['innerBlocks'][2] = Utils\Functions::set_new_block(Utils\Constants::BLOCK_EXCERPT, []);
        $block['innerBlocks'][3] = self::get_posts_list_meta_group_block();
        $block['innerHTML'] = Utils\M034Helper::POST_DATA_GROUP_BLOCK['html'];
        $block['innerContent'] = Utils\M034Helper::POST_DATA_GROUP_BLOCK['content'];
        return $block;
    }
}
