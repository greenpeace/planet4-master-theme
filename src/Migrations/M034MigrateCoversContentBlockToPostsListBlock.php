<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Block_Parser;

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
    protected static function execute(MigrationRecord $record): void
    {
        try {
            // Get the list of posts using Covers blocks.
            $posts = Utils\Functions::get_posts_using_specific_block(
                Utils\Constants::BLOCK_COVERS,
                Utils\Constants::ALL_POST_TYPES,
                Utils\Constants::POST_STATUS_LIST,
            );

            // If there are no posts, abort.
            if (!$posts) {
                return;
            }

            echo "Covers content block migration in progress...\n"; // phpcs:ignore

            $parser = new WP_Block_Parser();

            foreach ($posts as $post) {
                if (empty($post->post_content)) {
                    continue;
                }

                $current_post_id = $post->ID; // Store the current post ID

                echo 'Parsing post ', $current_post_id, "\n"; // phpcs:ignore

                // Get all the blocks of each post.
                $blocks = $parser->parse($post->post_content);

                if (!is_array($blocks)) {
                    throw new \Exception("Invalid block structure for post #" . $current_post_id);
                }

                foreach ($blocks as &$block) {
                    // Check if the block is valid.
                    if (!is_array($block)) {
                        continue;
                    }

                    // Check if the block has a 'blockName' key.
                    if (!isset($block['blockName'])) {
                        continue;
                    }

                    // Check if the block is a Cover block. If not, abort.
                    if ($block['blockName'] !== Utils\Constants::BLOCK_COVERS) {
                        continue;
                    }

                    // Check if the Cover block type is Content. If not, abort.
                    // Cover blocks of type content have as value of $block['attrs']['cover_type']
                    // the following possibilities: "content", "1", NULL
                    // https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/26b480a0954667a0813ca8c3a90377f2a1fbea1c/classes/blocks/class-covers.php#L35
                    // https://github.com/greenpeace/planet4-plugin-gutenberg-blocks/blob/26b480a0954667a0813ca8c3a90377f2a1fbea1c/classes/blocks/class-covers.php#L191
                    $type = $block['attrs']['cover_type'];
                    $const = Utils\Constants::COVER_BLOCK_TYPES['content'];

                    // phpcs:disable Use early exit to reduce code nesting
                    if (!isset($type) || $type === $const['name'] || $type === $const['number']) {
                        // Get the block attributes.
                        $attrs = self::get_posts_list_block_attrs($block);

                        // Transform the cover block into a posts list block.
                        $block = self::create_query_block($attrs);
                    }
                    // phpcs:enable Use early exit to reduce code nesting
                }

                // Unset the reference to prevent potential issues.
                unset($block);

                // Serialize the blocks content.
                $new_content = serialize_blocks($blocks);

                $post_update = array(
                    'ID' => $current_post_id,
                    'post_content' => $new_content,
                );

                // Update the post with the replaced blocks.
                $result = wp_update_post($post_update);

                if ($result === 0) {
                    throw new \Exception("There was an error trying to update the post #" . $current_post_id);
                }

                echo "Migration successful\n";
            }
        } catch (\ErrorException $e) {
            // Catch any exceptions and display the post ID if available
            echo "Migration wasn't executed for post ID: ", $current_post_id ?? 'unknown', "\n";
            echo $e->getMessage(), "\n";
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

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
     * @param array $existing_block_attrs - The attributes of the Covers block.
     * @return array - The new block.
     */
    private static function create_query_block(array $existing_block_attrs): array
    {
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
