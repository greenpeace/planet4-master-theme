<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Posts List blocks with errors to Posts List blocks updated.
 */
class M042TestingPL extends MigrationScript
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
     * Check whether a block is a Posts List block.
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

        // Check if the block is a Posts List block.
        return $block['blockName'] === Utils\Constants::BLOCK_QUERY;
    }

    /**
     * Create a new Query block based on attributes of the existing Posts List block.
     *
     * @param array $block - The current posts list block.
     * @return array - The new block.
     */
    private static function transform_block(array $block): array
    {
        $existing_block_attrs = self::get_posts_list_block_attrs($block);

        $categories = $existing_block_attrs['categories'];
        $tags = $existing_block_attrs['tags'];
        $post_type = $existing_block_attrs['post_type'];
        $layout_type = $existing_block_attrs['layout']['type'];
        $layout_count = $existing_block_attrs['layout']['columnCount'];
        $classname = $layout_type === 'default' ? 'list' : 'grid';
        $exclude = $existing_block_attrs['exclude'];
        $per_page = $existing_block_attrs['per_page'];
        $post_in = $existing_block_attrs['post_in'];

        $attrs = self::set_query_block_attrs($categories, $tags, $post_type, $layout_type, $layout_count, $exclude, $per_page, $post_in);

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
     * Get the attributes of a Post lists block.
     *
     * @param array $existing_block - The current Posts List block.
     * @return array - The attributes.
     */
    private static function get_posts_list_block_attrs(array $existing_block): array
    {
        $attrs = $existing_block['attrs'];
        $layout_default = [
            'type' => 'default',
            'columnCount' => 3,
        ];

        return [
            'title' => 'Test Title',
            'description' => 'Test Description',
            'categories' => isset($attrs['query']['taxQuery']['p4-page-type']) ? $attrs['query']['taxQuery']['p4-page-type'] : [],
            'tags' => isset($attrs['query']['taxQuery']['post_tag']) ? $attrs['query']['taxQuery']['post_tag'] : [],
            'post_type' => isset($attrs['query']['postType']) ? $attrs['query']['postType'] : [],
            'layout' => isset($attrs['layout']) ? $attrs['layout'] : $layout_default,
            'exclude' => isset($attrs['query']['exclude']) ? $attrs['query']['exclude'] : [],
            'per_page' => isset($attrs['query']['perPage']) ? $attrs['query']['perPage'] : 3,
            'post_in' => isset($attrs['query']['postIn']) ? $attrs['query']['postIn'] : [],
        ];
    }

    /**
     * Set all the attributes to create a new Query block.
     *
     * @param array $categories - The list of terms of the "p4-page-type" taxonomy.
     * @param array $tags - The list of post tags.
     * @param string $post_type - Type of posts to display (page, post etc).
     * @param string $layout_type - The layout type (grid or flex).
     * @param int $layout_count - The number of posts to show per layout.
     * @param array $exclude - Posts to exclude from the block.
     * @param int $per_page - The number of elements per page.
     * @param int $post_in - Posts to include to the block.
     * @return array - The attributes.
     */
    private static function set_query_block_attrs(
        array $categories,
        array $tags,
        string $post_type,
        string $layout_type,
        int $layout_count,
        array $exclude,
        int $per_page,
        array $post_in,
    ): array {
        $query = [];
        $query['pages'] = 0;
        $query['offset'] = 0;
        $query['postType'] = $post_type;
        $query['order'] = 'desc';
        $query['orderBy'] = 'date';
        $query['author'] = '';
        $query['search'] = '';
        $query['exclude'] = $exclude;
        $query['sticky'] = '';
        $query['inherit'] = false;
        $query['hasPassword'] = false;
        $query['postIn'] = $post_in;

        if (!empty($per_page)) {
            $query['perPage'] = $per_page;
        }

        if (empty($per_page)) {
            if ($layout === 'default') {
                $query['perPage'] = 3;
            } else {
                $query['perPage'] = 4;
            }
        }

        if (!empty($tags)) {
            $query['taxQuery']['post_tag'] = $tags;
        }
        if (!empty($categories)) {
            $query['taxQuery']['p4-page-type'] = $categories;
        }

        $layout = [];
        $layout['type'] = $layout_type;
        $layout['columnCount'] = $layout_count;

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
                    __('Prev', 'planet4-blocks'),
                ),
                Utils\Functions::create_block_single_button(
                    ['className' => 'carousel-control-next'],
                    __('Next', 'planet4-blocks'),
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
                            ['isLink' => true]
                        ),
                        Utils\Functions::create_group_block(
                            [
                                Utils\Functions::create_group_block(
                                    [
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_TERMS,
                                            ['term' => 'category', 'separator' => ' | ']
                                        ),
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_TERMS,
                                            ['term' => 'post_tag', 'separator' => ' ']
                                        ),
                                    ],
                                    ['layout' => ['type' => 'flex']]
                                ),
                                Utils\Functions::create_new_block(
                                    Utils\Constants::BLOCK_TITLE,
                                    ['isLink' => true]
                                ),
                                Utils\Functions::create_new_block(
                                    Utils\Constants::BLOCK_EXCERPT
                                ),
                                Utils\Functions::create_group_block(
                                    [
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_AUTHOR,
                                            ['isLink' => true]
                                        ),
                                        Utils\Functions::create_new_block(
                                            Utils\Constants::BLOCK_DATE
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
     * @return mixed - The navigation link block if the All Stories page is set.
     */
    private static function get_nav_links_block(): array
    {
        if (!get_option('page_for_posts')) {
            return [];
        }

        return Utils\Functions::create_new_block(
            Utils\Constants::BLOCK_NAV_LINK,
            [
                'label' => __('See all stories', 'planet4-blocks'),
                'url' => get_permalink(get_option('page_for_posts')),
                'className' => 'see-all-link',
            ]
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
                    ['placeholder' => __('No posts found. (This default text can be edited)', 'planet4-blocks')],
                    __('No posts found.', 'planet4-blocks'),
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
                'placeholder' => __('Enter description', 'planet4-blocks'),
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


