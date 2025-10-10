<?php

namespace P4\MasterTheme;

/**
 * Class ImageHandler
 */
class ImageHandler
{
    /**
     * Credit meta field key
     *
     */
    public const CREDIT_META_FIELD = '_credit_text';

    /**
     * ImageHandler constructor.
     */
    public function __construct()
    {
        add_action('init', [$this, 'register_meta_fields']);
        add_action('after_setup_theme', [$this, 'add_image_sizes']);
        add_filter('register_block_type_args', [$this, 'register_core_blocks_callback']);
        add_filter('wp_image_editors', [$this, 'force_image_compression']);
        add_filter('wp_handle_upload_prefilter', [$this, 'image_type_validation']);
        add_filter('jpeg_quality', fn () => 60);
    }

    /**
     * Add extra image sizes as needed.
     */
    public function add_image_sizes(): void
    {
        add_image_size('retina-large', 2048, 1366, false);
        add_image_size('articles-medium-large', 510, 340, false);
    }

    /**
     * Force WordPress to use ImageCompression as image manipulation editor.
     */
    public function force_image_compression(): array
    {
        return [ImageCompression::class];
    }

    /**
     * Validate image type before WP processes it.
     * * @param array $file Associative array containing Image details
     */
    public function image_type_validation(array $file): array
    {
        // Only apply validation to images.
        $file_type = wp_check_filetype_and_ext($file['tmp_name'], $file['name']);
        if (strpos($file_type['type'], 'image') !== 0) {
            return $file;
        }

        $allowed_wp_img_ext = array('jpg', 'jpeg', 'png', 'gif', 'ico');

        if (!in_array(strtolower($file_type['ext']), $allowed_wp_img_ext)) {
            $file['error'] = 'Only JPG, PNG, ICO, and GIF images are allowed for upload.';
        }

        return $file;
    }

    /**
     * Declare meta fields
     */
    public function register_meta_fields(): void
    {
        // Credit for images, used in image caption.
        register_post_meta(
            'attachment',
            self::CREDIT_META_FIELD,
            [
                'show_in_rest' => true,
                'type' => 'string',
                'single' => true,
            ]
        );
    }

    /**
     * Override the Gutenberg core/image block render method output,
     * to add credit field in its caption text & image alt text as title.
     *
     * @param array  $attributes    Attributes of the Gutenberg core/image block.
     * @param string $content The image element HTML.
     *
     * @return string HTML content of image element with credit field in caption and alt text in image title.
     */
    public function p4_core_image_block_render(array $attributes, string $content): string
    {
        $image_id = isset($attributes['id']) ? trim(str_replace('attachment_', '', $attributes['id'])) : '';
        $img_post_meta = $image_id ? get_post_meta($image_id) : [];
        if (!$img_post_meta) {
            return $content;
        }

        $credit = $img_post_meta[self::CREDIT_META_FIELD][0] ?? '';
        $alt_text = $img_post_meta['_wp_attachment_image_alt'][0] ?? '';

        if ($alt_text) {
            $content = str_replace(' alt=', ' title="' . esc_attr($alt_text) . '" alt=', $content);
        }

        $image_credit = ' ' . $credit;
        if (false === strpos($credit, '©')) {
            $image_credit = ' ©' . $image_credit;
        }

        $caption = '';

        $pattern = '/<figcaption[^>]*>(.*?)<\/figcaption>/';
        if (preg_match($pattern, $content, $matches)) {
            $caption = $matches[1];
        }

        if (empty($credit) || (!empty($caption) && strpos($caption, $image_credit) !== false)) {
            return $content;
        }

        return str_replace(
            empty($caption) ? '</figure>' : $caption,
            empty($caption) ?
                '<figcaption>' . esc_attr($image_credit) . '</figcaption></figure>' :
                $caption . esc_attr($image_credit),
            $content
        );
    }

    /**
     * Add callback function to Gutenberg core/image block.
     *
     * @param array $args Parameters given during block register.
     *
     * @return array Parameters of the block.
     */
    public function register_core_blocks_callback(array $args): array
    {
        if ('core/image' === $args['name']) {
            $args['render_callback'] = [$this, 'p4_core_image_block_render'];
        }

        return $args;
    }
}
