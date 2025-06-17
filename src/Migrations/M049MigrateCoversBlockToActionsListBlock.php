<?php

// phpcs:disable Generic.Files.LineLength.MaxExceeded

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Migrate Covers blocks (Actions type) to Actions List blocks.
 */
class M049MigrateCoversBlockToActionsListBlock extends MigrationScript
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
     * Check whether a block is a Covers block.
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

        // Check if the block is a Covers block.
        return $block['blockName'] === Utils\Constants::BLOCK_COVERS;
    }

    /**
     * Create a new Query block based on attributes of a Covers block.
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
     * Get the attributes from a Covers block.
     */
    private static function get_posts_list_block_attrs(array $existing_block): array
    {
        $attrs = $existing_block['attrs'];

        return [
            'title' => $attrs['title'] ?? '',
            'description' => $attrs['description'] ?? '',
            'posts' => $attrs['posts'] ?? [],
            'post_types' => $attrs['post_types'] ?? [],
            'current_post_id' => $attrs['current_post_id'] ?? 0,
            'layout' => isset($attrs['layout']) ? $attrs['layout'] : 'grid',
            'per_page' => $attrs['initialRowsLimit'] ?? 1,
            'additional_class' => $attrs['additional_class'] ?? '',
        ];
    }

    /**
     * Set all the attributes to create a new Query block.
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
        } elseif ($per_page === 2) {
            $items_per_page = 6;
        } else {
            $items_per_page = 100;
        }

        // If the layout is carousel (flex), we need at least 4 items per page.
        if ($layout_type === 'flex' && $items_per_page < 4) {
            $items_per_page = 4;
        }

        $query = [
            'pages' => 0,
            'perPage' => $items_per_page,
            'offset' => 0,
            'author' => '',
            'search' => '',
            'exclude' => [$current_post_id],
            'sticky' => '',
            'inherit' => false,
            'postType' => Utils\Constants::POST_TYPES_ACTION,
            'block_name' => Utils\Constants::BLOCK_ACTIONS_LIST,
            'postIn' => $posts_override,
            'hasPassword' => false,
            'order' => 'desc',
            'orderBy' => 'date',
        ];

        $layout = [
            'type' => $layout_type,
            'columnCount' => 3,
        ];

        $attrs = [
            'queryId' => 1,
            'query' => $query,
            'namespace' => Utils\Constants::BLOCK_ACTIONS_LIST,
            'className' => $classname,
            'layout' => $layout,
        ];

        return $attrs;
    }

    /**
     * Create and get a new buttons container block.
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
     * Create and get a new heading block.
     */
    private static function get_head_block(string $title): array
    {
        return Utils\Functions::create_block_heading(
            ['lock' => ['move' => true]],
            $title
        );
    }
}
