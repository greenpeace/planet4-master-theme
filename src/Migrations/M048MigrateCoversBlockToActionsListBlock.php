<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Articles block to Posts List blocks.
 */
class M048MigrateCoversBlockToActionsListBlock extends MigrationScript
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
     * Check whether a block is an Articles block.
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

        if ($block['attrs']['namespace'] === 'planet4-blocks/actions-list') {
            var_dump($block);
        }

        // Check if the block is a Covers block.
        return $block['blockName'] === Utils\Constants::BLOCK_COVERS;
    }

    /**
     * Create a new Query block based on attributes of an Articles block.
     *
     * @param array $block - The current articles block.
     * @return array - The new block.
     */
    private static function transform_block(array $block): array
    {
        $existing_block_attrs = self::get_posts_list_block_attrs($block);

        $posts_override = $existing_block_attrs['posts'];
        $layout_type = $existing_block_attrs['layout'] === 'carousel' ? 'flex' : 'grid';
        $classname = Utils\Constants::ACTIONS_LIST . ' p4-query-loop is-custom-layout-' . $existing_block_attrs['layout'];
        $per_page = $existing_block_attrs['per_page'];
        $current_post_id = $existing_block_attrs['current_post_id'];
        $additional_class = $existing_block_attrs['additional_class'];

        $attrs = self::set_query_block_attrs($posts_override, $layout_type, $per_page, $current_post_id, $classname);

        $inner_blocks = [
            self::get_head_block($existing_block_attrs['title']),
            self::get_paragraph_block($existing_block_attrs['description']),
            self::get_query_no_results_block(),
            self::get_post_template(),
            self::get_buttons_block(),
        ];

        return Utils\Functions::create_block_query(
            $inner_blocks,
            $attrs,
            $additional_class ? $classname . ' ' . $additional_class : $classname,
            Utils\Constants::ACTIONS_LIST
        );
    }

    /**
     * Get the attributes of a Post lists block.
     *
     * @param array $existing_block - The current Articles block.
     * @return array - The attributes.
     */
    private static function get_posts_list_block_attrs(array $existing_block): array
    {
        $attrs = $existing_block['attrs'];

        return [
            'title' => isset($attrs['title']) ? $attrs['title'] : '',
            'description' => isset($attrs['description']) ? $attrs['description'] : '',
            'posts' => isset($attrs['posts']) ? $attrs['posts'] : [],
            'post_types' => isset($attrs['post_types']) ? $attrs['post_types'] : [],
            'current_post_id' => isset($attrs['current_post_id']) ? $attrs['current_post_id'] : 0,
            'layout' => isset($attrs['layout']) ? $attrs['layout'] : 'grid',
            'per_page' => isset($attrs['initialRowsLimit']) ? $attrs['initialRowsLimit'] : 3,
            'additional_class' => isset($attrs['additional_class']) ? $attrs['additional_class'] : '',
        ];
    }

    /**
     * Set all the attributes to create a new Query block.
     *
     * @param array $posts_override - The list of posts to include in the query.
     * @param string $layout_type - The layout type.
     * @param int $per_page - The number of elements per page.
     * @param int $current_post_id - The current post ID.
     * @return array - The attributes.
     */
    private static function set_query_block_attrs(
        array $posts_override,
        string $layout_type,
        int $per_page,
        int $current_post_id,
        string $classname,
    ): array {

        if ($per_page === 1) {
            $items_per_page = 3;
        }
        else if ($per_page === 2) {
            $items_per_page = 6;
        } else {
            $items_per_page = 100;
        }

        $query = [];
        $query['pages'] = 0;
        $query['perPage'] = $items_per_page;
        $query['offset'] = 0;
        $query['author'] = '';
        $query['search'] = '';
        $query['exclude'] = [$current_post_id];
        $query['sticky'] = '';
        $query['inherit'] = false;
        $query['postType'] = Utils\Constants::POST_TYPES_ACTION;
        $query['postIn'] = $posts_override;
        $query['hasPassword'] = false;
        $query['order'] = 'desc';
        $query['orderBy'] = 'date';

        $layout = [];
        $layout['type'] = $layout_type;
        $layout['columnCount'] = 3;

        $attrs = [];
        $attrs['queryId'] = 1;
        $attrs['query'] = $query;
        $attrs['namespace'] = Utils\Constants::BLOCK_ACTIONS_LIST;
        $attrs['className'] = $classname;
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
                Utils\Functions::create_new_block(
                    Utils\Constants::BLOCK_FEAT_IMAGE,
                    ['isLink' => true]
                ),
                Utils\Functions::create_group_block(
                    [
                        Utils\Functions::create_new_block(
                            Utils\Constants::P4_OTHER_BLOCKS['breadcrumb'],
                            ['post_type' => Utils\Constants::POST_TYPES_ACTION]
                        ),
                        Utils\Functions::create_new_block(
                            Utils\Constants::BLOCK_TITLE,
                            ['isLink' => true]
                        ),
                        Utils\Functions::create_new_block(
                            Utils\Constants::BLOCK_EXCERPT
                        ),
                    ],
                    []
                ),
                Utils\Functions::create_group_block(
                    [
                        Utils\Functions::create_new_block(
                            Utils\Constants::ACTION_BUTTON,
                        ),
                    ],
                    ['className' => 'read-more-nav',]
                ),
            ],
            [
                'lock' => [
                    'move' => true,
                    'remove' => true,
                ],
            ],
            Utils\Constants::ACTIONS_LIST,
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
     * Create and get a new group block for the PostList block.
     *
     * @param string $title - The block title.
     * @return array - The new block.
     */
    private static function get_head_block(string $title): array
    {
        return Utils\Functions::create_block_heading(
            ['lock' => ['move' => true]],
            $title
        );
    }
}
