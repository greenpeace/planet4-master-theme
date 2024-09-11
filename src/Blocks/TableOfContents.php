<?php

/**
 * TableOfContents block class.
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

 namespace P4\MasterTheme\Blocks;

/**
 * Class TableOfContents
 *
 * @package P4\MasterTheme\Blocks
 */
class TableOfContents extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'submenu';
    //TO DO: Change the name of this block to 'table-of-contents' when migrating it.

    /**
     * TableOfContents constructor.
     */
    public function __construct()
    {

        $this->register_table_of_contents_block();
    }

    /**
     * Register TableOfContents block.
     */
    public function register_table_of_contents_block(): void
    {
        register_block_type(
            self::get_full_block_name(),
            [
                // todo: Remove when all content is migrated.
                'render_callback' => [ self::class, 'render_frontend' ],
                'attributes' => [
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'table_of_contents_style' => [ // Needed for old blocks conversion.
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    /**
                     * Levels is an array of objects.
                     * Object structure:
                     * {
                     *   heading: 'integer'
                     *   link: 'boolean'
                     *   style: 'string'
                     * }
                     */
                    'levels' => [
                        'type' => 'array',
                        'items' => [
                            'type' => 'object',
                            // In JSON Schema you can specify object properties in the properties attribute.
                            'properties' => [
                                'heading' => [
                                    'type' => 'integer',
                                ],
                                'link' => [
                                    'type' => 'boolean',
                                ],
                                'style' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                        'default' => [
                            [
                                'heading' => 2,
                                'link' => false,
                                'style' => 'none',
                            ],
                        ],
                    ],
                ],
            ]
        );

        add_action('enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ]);
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ]);
    }

    /**
     * Required by the `Base_Block` class.
     *
     * @param array $fields Unused, required by the abstract function.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $fields): array
    {
        return [];
    }
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
