<?php

namespace P4\MasterTheme\Blocks;

class Register
{
    /**
     * Columns block constants.
     */
    private const COLUMNS_LAYOUT_NO_IMAGE = 'no_image';
    private const COLUMNS_LAYOUT_TASKS = 'tasks';
    private const COLUMNS_LAYOUT_IMAGES = 'image';
    private const COLUMNS_MAX = 4;

    /**
     * Load blocks registered from assets (block.json).
     */
    public static function register(): void
    {
        // Blocks whose render callback only forwards to render_frontend_from_blockname().
        $simple_blocks = [
            'Cookies' => 'planet4-blocks/cookies',
            'HappyPoint' => 'planet4-blocks/happypoint',
            'TableOfContents' => 'planet4-blocks/submenu',
            'TopicLink' => 'planet4-blocks/topic-link',
        ];

        foreach ($simple_blocks as $asset_name => $block_name) {
            self::registerFromAssets($asset_name, [
                'render_callback' => function ($attributes) use ($block_name) {
                    return BaseBlock::render_frontend_from_blockname($attributes, $block_name);
                },
            ]);
        }

        // No render callback.
        self::registerFromAssets('Spreadsheet');

        // Needs to modify the attributes before rendering.
        self::registerFromAssets('Columns', [
            'render_callback' => function ($attributes) {
                $attributes['columns'] = self::get_columns_data($attributes);

                return BaseBlock::render_frontend_from_blockname($attributes, 'planet4-blocks/columns');
            },
        ]);
    }

    /**
     * Get all the data needed to render the Columns block correctly.
     *
     * @param array $attributes The block attributes.
     *
     * @return array The columns, with attachment IDs resolved to image src and srcset.
     */
    private static function get_columns_data(array $attributes): array
    {
        $columns_block_style = $attributes['columns_block_style'] ?? self::COLUMNS_LAYOUT_NO_IMAGE;
        $columns = array_slice($attributes['columns'] ?? [], 0, self::COLUMNS_MAX);

        if (self::COLUMNS_LAYOUT_NO_IMAGE === $columns_block_style) {
            return $columns;
        }

        // The image size depends on the layout chosen and the number of columns.
        $image_size = self::get_columns_image_size($columns_block_style, count($columns));

        foreach ($columns as $key => $column) {
            $attachment = $column['attachment'] ?? 0;
            if (0 === $attachment) {
                continue;
            }

            [$img_src] = wp_get_attachment_image_src($attachment, $image_size);

            $columns[$key]['attachment'] = $img_src;
            $columns[$key]['attachment_srcset'] = wp_get_attachment_image_srcset($attachment, $image_size);
        }

        return $columns;
    }

    /**
     * Which image size should be used for a combination of layout style and number of columns?
     *
     * @param string $columns_block_style The columns style that was picked for the block.
     * @param int    $number_columns The total number of columns in the block.
     *
     * @return string The image size.
     */
    private static function get_columns_image_size(string $columns_block_style, int $number_columns): string
    {
        if (in_array($columns_block_style, [self::COLUMNS_LAYOUT_TASKS, self::COLUMNS_LAYOUT_IMAGES], true)) {
            return $number_columns >= 2 ? 'articles-medium-large' : 'large';
        }

        return 'thumbnail';
    }

    /**
     * Requires a file `block.json`
     * in assets/(src|build)/blocks/{blockDirName}/
     */
    public static function registerFromAssets(
        string $blockDirName,
        array $properties = []
    ): void {
        register_block_type(
            get_template_directory() . '/assets/build/blocks/' . $blockDirName,
            $properties
        );
    }
}
