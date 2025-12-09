<?php

/**
 * Columns block class
 *
 * @package P4\MasterTheme;
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

/**
 * Class Columns
 *
 * @package P4\MasterTheme\Blocks
 */
class Columns extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'columns';

    public const TASK_TEMPLATE_NAME = 'tasks';

    public const LAYOUT_NO_IMAGE = 'no_image';
    public const LAYOUT_TASKS = 'tasks';
    public const LAYOUT_ICONS = 'icons';
    public const LAYOUT_IMAGES = 'image';
    public const MAX_COLUMNS = 4;

    /**
     * Columns constructor.
     */
    public function __construct()
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'api_version' => 3,
                'editor_script' => 'planet4-blocks',
                'render_callback' => static function ($attributes) {
                    $attributes['columns'] = self::get_columns_data($attributes);

                    return self::render_frontend($attributes);
                },
                'attributes' => [
                    'columns_block_style' => [
                        'type' => 'string',
                        'default' => self::LAYOUT_NO_IMAGE,
                    ],
                    'columns_title' => [
                        'type' => 'string',
                    ],
                    'columns_description' => [
                        'type' => 'string',
                    ],
                    'columns' => [
                        'type' => 'array',
                        'default' => [],
                        'items' => [
                            'type' => 'object',
                            // In JSON Schema you can specify object properties in the properties attribute.
                            'properties' => [
                                'title' => [
                                    'type' => 'string',
                                ],
                                'description' => [
                                    'type' => 'string',
                                ],
                                'attachment' => [
                                    'type' => 'integer',
                                    'default' => 0,
                                ],
                                'cta_link' => [
                                    'type' => 'string',
                                ],
                                'link_new_tab' => [
                                    'type' => 'boolean',
                                ],
                                'cta_text' => [
                                    'type' => 'string',
                                ],
                            ],
                        ],
                    ],
                ],
            ]
        );
    }

    /**
     * Required by the `BaseBlock` class.
     *
     * @param array $fields Unused, required by the abstract function.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $fields): array
    {
        return [];
    }
    //phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Get all the data that will be needed to render the block correctly.
     *
     * @param array $attributes This is the array of fields of this block.
     *
     * @return array The data to be passed in the View.
     */
    private static function get_columns_data(array $attributes): array
    {
        $columns_block_style = $attributes['columns_block_style'];

        $columns = $attributes['columns'];

        $columns = array_slice($columns, 0, self::MAX_COLUMNS);

        // Define the image size that will be used, based on layout chosen and number of columns.
        $number_columns = count($columns);
        if (self::LAYOUT_NO_IMAGE !== $columns_block_style) {
            $image_size = self::get_image_size($columns_block_style, $number_columns);

            foreach ($columns as $key => $column) {
                $attachment = $column['attachment'] ?? 0;
                if (0 === $attachment) {
                    continue;
                }
                [$img_src] = wp_get_attachment_image_src($attachment, $image_size);

                $columns[$key]['attachment'] = $img_src;
                $columns[$key]['attachment_srcset'] = wp_get_attachment_image_srcset(
                    $attachment,
                    $image_size
                );
            }
        }

        return $columns;
    }

    /**
     * Which image size should be used for a combination of layout style and number of columns?
     *
     * @param string $columns_block_style The columns style that was picked for the block.
     * @param int    $number_columns The total number of columns in the block.
     * @return string The image size.
     */
    private static function get_image_size(string $columns_block_style, int $number_columns): string
    {
        if (in_array($columns_block_style, [self::LAYOUT_TASKS, self::LAYOUT_IMAGES], true)) {
            if ($number_columns >= 2) {
                return 'articles-medium-large';
            }

            return 'large';
        }

        return 'thumbnail';
    }
}
