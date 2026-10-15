<?php

namespace P4\MasterTheme\Blocks;

class Register
{
    private const BLOCK_PREFIX = 'planet4-blocks';
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
        $blocks = [
            'Cookies' => [
                'block' => self::BLOCK_PREFIX . '/cookies',
            ],
            'HappyPoint' => [
                'block' => self::BLOCK_PREFIX . '/happypoint',
            ],
            'TableOfContents' => [
                'block' => self::BLOCK_PREFIX . '/submenu',
            ],
            'TopicLink' => [
                'block' => self::BLOCK_PREFIX . '/topic-link',
            ],
            'SocialMedia' => [
                'block' => self::BLOCK_PREFIX . '/social-media',
                'enqueue' => [self::class, 'enqueue_instagram_embed'],
            ],
            'Columns' => [
                'block' => self::BLOCK_PREFIX . '/columns',
                'attributes' => function (array $attributes): array {
                    $attributes['columns'] = self::get_columns_data($attributes);
                    return $attributes;
                },
            ],
            'Spreadsheet' => [],
            'Counter' => [],
        ];

        foreach ($blocks as $asset_name => $config) {
            $properties = [];

            if (isset($config['block'])) {
                $properties['render_callback'] = function (array $attributes) use ($config): string {
                    if (isset($config['enqueue'])) {
                        $config['enqueue']();
                    }
                    if (isset($config['attributes'])) {
                        $attributes = $config['attributes']($attributes);
                    }
                    return BaseBlock::render_frontend_from_blockname($attributes, $config['block']);
                };
            }

            register_block_type(
                get_template_directory() . '/assets/build/blocks/' . $asset_name,
                $properties
            );
        }
    }

    /**
     * Enqueue the Instagram embed script.
     */
    public static function enqueue_instagram_embed(): void
    {
        wp_enqueue_script(
            'instagram-embed',
            'https://www.instagram.com/embed.js',
            [],
            null,
            [
                'in_footer' => true,
                'strategy' => 'async',
            ]
        );
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
}
