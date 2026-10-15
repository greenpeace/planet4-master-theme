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
     * Gallery block constants.
     */
    private const GALLERY_LAYOUT_SLIDER = 1;
    private const GALLERY_LAYOUT_THREE_COLUMNS = 2;
    private const GALLERY_LAYOUT_GRID = 3;
    private const GALLERY_CLASS_LAYOUT_THREE_COLUMNS = 'is-style-three-columns';

    /**
     * Load blocks registered from assets (block.json).
     */
    public static function register(): void
    {
        $blocks = [
            'CarouselHeader' => [
                'block' => self::BLOCK_PREFIX . '/carousel-header',
                'attributes' => function (array $attributes, string $content): array|string {
                    if (! empty($content)) {
                        return $content;
                    }

                    if (! empty($attributes['slides']) && empty($attributes['slides'][0]['image_url'])) {
                        $attributes['slides'] = self::get_carousel_header_slides_image_data($attributes['slides']);
                    }

                    return $attributes;
                },
            ],
            'Columns' => [
                'attributes' => function (array $attributes): array {
                    $attributes['columns'] = self::get_columns_data($attributes);
                    return $attributes;
                },
                'block' => self::BLOCK_PREFIX . '/columns',
            ],
            'Cookies' => [
                'block' => self::BLOCK_PREFIX . '/cookies',
            ],
            'Counter' => [],
            'Gallery' => [
                'attributes' => function (array $attributes): array {
                    $attributes['images'] = self::get_gallery_block_images($attributes);
                    return $attributes;
                },
                'block' => self::BLOCK_PREFIX . '/gallery',
                'hydrate' => true,
            ],
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
                'attributes' => function (array $attributes): array {
                    return self::get_take_action_boxout_data($attributes);
                },
                'block' => self::BLOCK_PREFIX . '/take-action-boxout',
            ],
            'TopicLink' => [
                'block' => self::BLOCK_PREFIX . '/topic-link',
            ],
        ];

        foreach ($blocks as $asset_name => $config) {
            $properties = [];

            if (isset($config['block'])) {
                $properties['render_callback'] = self::block_render_callback($config);
            }

            register_block_type(
                get_template_directory() . '/assets/build/blocks/' . $asset_name,
                $properties
            );
        }

        // Remove decoding attribute from the first image in the carousel header block
        // to avoid lazy loading it and improve LCP.
        add_filter('wp_img_tag_add_decoding_attr', [self::class, 'carousel_header_first_image_decoding'], 10, 2);
    }

    /**
     * Disable the decoding attribute for the first image of the Carousel Header block.
     *
     * @param string|false|null $value The decoding attribute value.
     * @param string            $image The HTML `img` tag.
     *
     * @return string|false|null The decoding attribute value, or false for the first Carousel Header image.
     */
    public static function carousel_header_first_image_decoding($value, string $image)
    {
        if (str_contains($image, 'carousel-header-image--first')) {
            return false;
        }

        return $value;
    }

    /**
     * Builds the render callback for a block from its config.
     *
     * @param array $config Block config
     */
    private static function block_render_callback(array $config): callable
    {
        return function (array $attributes, string $content = '') use ($config): string {
            if (isset($config['enqueue'])) {
                $config['enqueue']();
            }

            if (isset($config['attributes'])) {
                $attributes = $config['attributes']($attributes);
            }

            // For the Take Action Boxout block we have a special render function.
            if ($config['block'] === self::BLOCK_PREFIX . '/take-action-boxout') {
                return self::render_take_action_boxout($attributes);
            }

            if (!empty($config['hydrate'])) {
                return BaseBlock::hydrate_frontend($attributes, $content, $config['block']);
            }

            return BaseBlock::render_frontend_from_blockname($attributes, $config['block']);
        };
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
     * Get the images data that will be needed to render the block correctly.
     *
     * @param array $fields This is the array of fields of this block.
     *
     * @return array The images to be passed in the View.
     */
    public static function get_gallery_block_images(array $fields): array
    {
        $images = [];

        if (isset($fields['multiple_image']) && '' !== $fields['multiple_image']) {
            $exploded_images = explode(',', $fields['multiple_image']);
        } else {
            $exploded_images = [];
        }

        if (isset($fields['gallery_block_focus_points'])) {
            $img_focus_points = json_decode(str_replace("'", '"', $fields['gallery_block_focus_points']), true);
        } else {
            $img_focus_points = [];
        }

        $image_sizes = [
            self::GALLERY_LAYOUT_SLIDER => 'retina-large',
            self::GALLERY_LAYOUT_THREE_COLUMNS => 'medium_large',
            self::GALLERY_LAYOUT_GRID => 'large',
        ];

        foreach ($exploded_images as $image_id) {
            $image_size = $fields['gallery_image_size'] ?? (
                $fields['gallery_block_style'] ? $image_sizes[$fields['gallery_block_style']] : null
            );

            $image_data = [];

            $image_data_array = wp_get_attachment_image_src($image_id, $image_size);
            $image_data['image_src'] = $image_data_array ? $image_data_array[0] : '';
            $image_data['image_srcset'] = wp_get_attachment_image_srcset(
                $image_id,
                $image_size,
                wp_get_attachment_metadata($image_id)
            );
            $image_data['image_sizes'] = wp_calculate_image_sizes($image_size, null, null, $image_id);
            $image_data['alt_text'] = get_post_meta($image_id, '_wp_attachment_image_alt', true);
            $image_data['caption'] = wp_get_attachment_caption($image_id);
            $image_data['focus_image'] = $img_focus_points[$image_id] ?? '';
            $attachment_fields = get_post_custom($image_id);
            $image_data['credits'] = '';
            if (isset($attachment_fields['_credit_text'][0]) && ! empty($attachment_fields['_credit_text'][0])) {
                $image_data['credits'] = $attachment_fields['_credit_text'][0];
                if (! is_numeric(strpos($attachment_fields['_credit_text'][0], '©'))) {
                    $image_data['credits'] = '© ' . $image_data['credits'];
                }
            }

            $images[] = $image_data;
        }

        // If the gallery style is "3 Columns" we need to limit the number of images to 3
        // to avoid the lightbox carousel to show more images than that.
        if (isset($fields['className']) && $fields['className'] === self::GALLERY_CLASS_LAYOUT_THREE_COLUMNS) {
            $images = array_slice($images, 0, 3);
        }

        return $images;
    }

    /**
     * Get image data for the Carousel Header slides.
     *
     * @param array $slides Slides of the block.
     *
     * @return array The slides, with image url, srcset and alt resolved from the attachment ID.
     */
    private static function get_carousel_header_slides_image_data(array $slides): array
    {
        foreach ($slides as &$slide) {
            try {
                $image_id = $slide['image'];
                $temp_array = wp_get_attachment_image_src($image_id, 'retina-large');
                if (false !== $temp_array && ! empty($temp_array)) {
                    $slide['image_url'] = $temp_array[0];
                    // phpcs:disable Generic.Files.LineLength.MaxExceeded
                    $slide['image_srcset'] = wp_get_attachment_image_srcset($image_id, 'retina-large', wp_get_attachment_metadata($image_id));
                    // phpcs:enable Generic.Files.LineLength.MaxExceeded
                }

                $temp_image = wp_prepare_attachment_for_js($image_id);
                $slide['image_alt'] = $temp_image['alt'] ?? '';
            } catch (\Exception $e) {
                function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            }
        }

        return $slides;
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
                'boxout' => [
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
                ],
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
            'boxout' => [
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
            ],
        ];
    }

        /**
     * Render the Take Action Boxout block in the frontend.
     *
     * @param array $attributes Block attributes.
     *
     * @return mixed
     */
    private static function render_take_action_boxout(array $attributes)
    {
        $block_output = \Timber\Timber::compile('take-action-boxout.twig', $attributes);

        // phpcs:ignore WordPress.WP.I18n.NonSingularStringLiteralText
        $empty_message = defined('static::EMPTY_MESSAGE') ? __(static::EMPTY_MESSAGE, 'planet4-master-theme') :
            "Block content is empty. Check the block's settings or remove it.";

        // Return empty string if rendered output contains only whitespace or new lines.
        // If it is a rest request from editor/admin area, return a message that block has no content.
        $empty_content = BaseBlock::is_rest_request() ? '<div class="EmptyMessage">' . $empty_message . '</div>' : '';

        return ctype_space($block_output) ? $empty_content : $block_output;
    }
}
