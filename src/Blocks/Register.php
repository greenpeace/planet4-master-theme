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
            'Columns' => [
                'block' => self::BLOCK_PREFIX . '/columns',
                'attributes' => function (array $attributes): array {
                    $attributes['columns'] = self::get_columns_data($attributes);
                    return $attributes;
                },
            ],
            'Cookies' => [
                'block' => self::BLOCK_PREFIX . '/cookies',
            ],
            'Counter' => [],
            'HappyPoint' => [
                'block' => self::BLOCK_PREFIX . '/happypoint',
            ],
            'SocialMedia' => [
                'block' => self::BLOCK_PREFIX . '/social-media',
                'enqueue' => [self::class, 'enqueue_instagram_embed'],
            ],
            'Spreadsheet' => [],
            'TableOfContents' => [
                'block' => self::BLOCK_PREFIX . '/submenu',
            ],
            'TakeActionBoxout' => [
                'block' => self::BLOCK_PREFIX . '/take-action-boxout',
                'attributes' => function (array $attributes): array {
                    $attributes['boxout'] = self::get_take_action_boxout_data($attributes);
                    return $attributes;
                },
            ],
            'TopicLink' => [
                'block' => self::BLOCK_PREFIX . '/topic-link',
            ],
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

    /**
     * Get all the data that will be needed to render the Take Action Boxout block correctly.
     *
     * @param array $fields This is the array of fields of this block.
     *
     * @return array The data to be passed in the View.
     */
    private static function get_take_action_boxout_data(array $fields): array
    {
        $page_id = $fields['take_action_page'] ?? '';

        if (empty($page_id)) {
            $img_id = $fields['imageId'] ?? $fields['background_image'] ?? null;
            if (! empty($img_id)) {
                [ $src ] = wp_get_attachment_image_src($img_id, 'large');

                $src_set = wp_get_attachment_image_srcset($img_id);
                $alt_text = get_post_meta($img_id, '_wp_attachment_image_alt', true);
            }

            return [
                'title' => $fields['custom_title'] ?? $fields['title'] ?? '',
                'excerpt' => $fields['custom_excerpt'] ?? $fields['excerpt'] ?? '',
                'link' => $fields['custom_link'] ?? $fields['link'] ?? '',
                'new_tab' => $fields['custom_link_new_tab'] ?? $fields['newTab'] ?? false,
                'link_text' => $fields['custom_link_text'] ?? $fields['linkText'] ?? '',
                'image' => $src ?? '',
                'image_alt' => $alt_text ?? '',
                'image_srcset' => $src_set ?? '',
                'stickyMobile' => $fields['stickyOnMobile'] ?? false,
                'headingFontSize' => $fields['headingFontSize'] ?? 'medium',
            ];
        }

        $args = [
            'p' => (int) $page_id, // ID of a page, post.
            'post_type' => 'any',
            'post_status' => 'publish',
        ];

        // Try to find the page that the user selected.
        $query = new \WP_Query($args);

        if (! $query->have_posts()) {
            return [];
        }

        // Populate the necessary fields for the block.
        $posts = $query->get_posts();
        $page = $posts[0];
        $options = get_option('planet4_options');

        if (has_post_thumbnail($page)) {
            $image = get_the_post_thumbnail_url($page, 'large');
            $img_id = get_post_thumbnail_id($page);
            $src_set = wp_get_attachment_image_srcset($img_id);
            $image_alt = get_post_meta($img_id, '_wp_attachment_image_alt', true);
        }

        $meta = get_post_meta($page_id);
        if (isset($meta['action_button_text']) && $meta['action_button_text'][0]) {
            $cover_button_text = $meta['action_button_text'][0];
        } else {
            $cover_button_text = $options['take_action_covers_button_text']
                ?? __('Take action', 'planet4-master-theme');
        }

        return [
            'title' => null === $page ? '' : $page->post_title,
            'excerpt' => null === $page ? '' : $page->post_excerpt,
            'link' => null === $page ? '' : get_permalink($page),
            'new_tab' => $fields['newTab'] ?? false,
            'link_text' => $cover_button_text,
            'image' => $image ?? '',
            'image_alt' => $image_alt ?? '',
            'image_srcset' => $src_set ?? '',
            'stickyMobile' => $fields['stickyOnMobile'] ?? false,
            'headingFontSize' => $fields['headingFontSize'] ?? 'medium',
            'page_id' => $page_id,
        ];
    }
}
