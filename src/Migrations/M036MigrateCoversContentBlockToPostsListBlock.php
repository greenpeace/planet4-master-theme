<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Covers block of type Content to Posts List blocks.
 */
class M036MigrateCoversContentBlockToPostsListBlock extends MigrationScript
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
        return !isset($type) || $type === Utils\Constants::COVER_TYPE_CONTENT || $type === Utils\Constants::OLD_COVER_TYPES['3'];
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

        $attrs = self::set_query_block_attrs($tags, $posts_override, $post_types, $layout_type);

        $inner_blocks = [
            self::get_head_group_block($existing_block_attrs['title']),
            self::get_paragraph_block($existing_block_attrs['description']),
            self::get_query_no_results_block(),
            self::get_post_template(),
            self::get_buttons_block(),
            self::get_nav_links_block(),
        ];

        return Utils\Functions::create_block_query(
            $inner_blocks,
            $attrs,
            $classname
        );
    }

    /**
     * Get the attributes of a Covers block.
     *
     * @param array $existing_block - The current Covers block.
     * @return array - The attributes.
     */
    private static function get_posts_list_block_attrs(array $existing_block): array
    {
        $attrs = $existing_block['attrs'];

        return [
            'title' => $attrs['title'] ?? '',
            'description' => $attrs['description'] ?? '',
            'cover_type' => $attrs['cover_type'] ?? 'content',
            'layout' => $attrs['layout'] ?? 'grid',
            'tags' => $attrs['tags'] ?? [],
            'posts' => $attrs['posts'] ?? [],
            'post_types' => $attrs['post_types'] ?? [],
        ];
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
    private static function set_query_block_attrs(
        array $tags,
        array $posts_override,
        array $post_types,
        string $layout_type
    ): array {
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
     * Create and get a new buttons container block.
     *
     * @return array - The new block.
     */
    private static function get_buttons_block(): array
    {
        return Utils\Functions::create_block_buttons(
            [
                'lock' => [
                    'move' => true,
                ],
                'layout' => [
                    'type' => 'flex',
                    'justifyContent' => 'space-between',
                    'orientation' => 'horizontal',
                    'flexWrap' => 'nowrap',
                ],
                'className' => 'carousel-controls',
            ],
            [
                Utils\Functions::create_block_single_button(
                    ['className' => 'carousel-control-prev'],
                    'Prev',
                ),
                Utils\Functions::create_block_single_button(
                    ['className' => 'carousel-control-next'],
                    'Next',
                ),
            ]
        );
    }

    /**
     * Create and get a new post template.
     *
     * @return array - The new template.
     */
    private static function get_post_template(): array
    {
        return Utils\Functions::create_post_template(
            [
                Utils\Functions::create_block_columns(
                    [],
                    [
                        Utils\Functions::create_new_block(
                            Utils\Constants::BLOCK_FEAT_IMAGE,
                            ['isLink' => true],
                            [],
                            '',
                            []
                        ),
                        Utils\Functions::create_group_block(
                            [
                                Utils\Functions::create_group_block(
                                    [
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_TERMS,
                                            [
                                                'term' => 'post_tag',
                                                'separator' => ' ',
                                            ],
                                            [],
                                            '',
                                            []
                                        ),
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_TERMS,
                                            [
                                                'term' => 'category',
                                                'separator' => ' | ',
                                            ],
                                            [],
                                            '',
                                            []
                                        ),
                                    ],
                                    ['className' => 'posts-list-meta']
                                ),
                                Utils\Functions::create_new_block(
                                    Utils\Constants::BLOCK_TITLE,
                                    [],
                                    [],
                                    '',
                                    []
                                ),
                                Utils\Functions::create_new_block(
                                    Utils\Constants::BLOCK_EXCERPT,
                                    [],
                                    [],
                                    '',
                                    []
                                ),
                                Utils\Functions::create_group_block(
                                    [
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_AUTHOR,
                                            ['isLink' => true],
                                            [],
                                            '',
                                            []
                                        ),
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_DATE,
                                            [],
                                            [],
                                            '',
                                            []
                                        ),
                                    ],
                                    ['className' => 'posts-list-meta',]
                                ),
                            ],
                            []
                        ),
                    ]
                ),
            ],
            [
                'lock' => [
                    'move' => true,
                    'remove' => true,
                ],
            ]
        );
    }

    /**
     * Create and get a new navigation link block.
     *
     * @return array - The new block.
     */
    private static function get_nav_links_block(): array
    {
        return Utils\Functions::create_new_block(
            Utils\Constants::BLOCK_NAV_LINK,
            [
                'label' => 'See all stories',
                'url' => '/news-stories/',
                'className' => 'see-all-link',
            ],
            [],
            '',
            []
        );
    }

    /**
     * Create and get a new query-no-results block.
     *
     * @return array - The new block.
     */
    private static function get_query_no_results_block(): array
    {
        return Utils\Functions::create_block_query_no_results(
            [
                Utils\Functions::create_block_paragraph(
                    [],
                    'No posts found. (This default text can be edited)'
                ),
            ],
            [],
        );
    }

    /**
     * Create and get a new paragraph block.
     *
     * @param string $description - The text for the paragraph.
     * @return array - The new block.
     */
    private static function get_paragraph_block(string $description): array
    {
        return Utils\Functions::create_block_paragraph(
            [
                'lock' => [
                    'move' => true,
                ],
                'placeholder' => 'Enter description',
                'style' => [
                    'spacing' => [
                        'margin' => [
                            'top' => '24px',
                            'bottom' => '36px',
                        ],
                    ],
                ],
            ],
            $description
        );
    }

    /**
     * Create and get a new group block for the Covers head.
     *
     * @param string $title - The block title.
     * @return array - The new block.
     */
    private static function get_head_group_block(string $title): array
    {
        return Utils\Functions::create_group_block(
            [
                Utils\Functions::create_block_heading(
                    ['lock' => ['move' => true]],
                    $title
                ),
                self::get_nav_links_block(),
            ],
            [
                'layout' => [
                    'type' => 'flex',
                    'justifyContent' => 'space-between',
                ],
            ]
        );
    }
}
