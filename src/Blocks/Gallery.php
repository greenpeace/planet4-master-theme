<?php

namespace P4\MasterTheme\Blocks;

use WP_Block_Type_Registry;

class Gallery extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'gallery';

    /**
     * Allowed Post types.
     *
     * @const array BLOCK_ALLOWED_POST_TYPES.
     */
    public const BLOCK_ALLOWED_POST_TYPES = [ 'page', 'campaign', 'post' ];

    public const LAYOUT_SLIDER = 1;
    public const LAYOUT_THREE_COLUMNS = 2;
    public const LAYOUT_GRID = 3;

    /**
     * Gallery constructor.
     */
    public function __construct()
    {
        if (WP_Block_Type_Registry::get_instance()->is_registered(self::get_full_block_name())) {
            return;
        }

        $this->register_gallery_block();

        add_action('wp_footer', [$this, 'add_lightbox_scripts'], 99);
    }

    /**
     * Register Gallery block.
     */
    public function register_gallery_block(): void
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'editor_script' => 'planet4-blocks',
                // todo: Remove when all content is migrated.
                'render_callback' => static function ($attributes, $content) {
                    $attributes['images'] = self::get_images($attributes);

                    return self::hydrate_frontend($attributes, $content);
                },
                'attributes' => [
                    'gallery_block_style' => [ // Needed for existing blocks conversion.
                        'type' => 'integer',
                        'default' => 0,
                    ],
                    'gallery_block_title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'gallery_block_description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'multiple_image' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'gallery_block_focus_points' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'image_data' => [
                        'type' => 'array',
                        'default' => [],
                        'items' => [
                            'type' => 'object',
                            'properties' => [
                                'id' => [
                                    'type' => 'integer',
                                ],
                                'url' => [
                                    'type' => 'string',
                                ],
                                'focalPoint' => [
                                    'type' => 'object',
                                ],
                            ],
                        ],
                    ],
                    'images' => [
                        'type' => 'array',
                        'default' => [],
                    ],
                    'expand_on_click' => [
                        'type' => 'boolean',
                        'default' => false,
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
    //phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

    /**
     * Load additional frontend assets
     */
    public static function enqueue_frontend_assets(): void
    {
        if (BlockList::has_block(self::get_full_block_name())) {
            wp_enqueue_script(
                'hammer',
                'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js',
                [],
                '2.0.8',
                true
            );
        }
        parent::enqueue_frontend_assets();
    }

    /**
     * Get the images data that will be needed to render the block correctly.
     *
     * @param array $fields This is the array of fields of this block.
     *
     * @return array The images to be passed in the View.
     */
    public static function get_images(array $fields): array
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

        $images_dimensions = [];
        $image_sizes = [
            self::LAYOUT_SLIDER => 'retina-large',
            self::LAYOUT_THREE_COLUMNS => 'medium_large',
            self::LAYOUT_GRID => 'large',
        ];

        foreach ($exploded_images as $image_id) {
            $image_size = $fields['gallery_image_size'] ?? (
                $fields['gallery_block_style'] ? $image_sizes[ $fields['gallery_block_style'] ] : null
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
            $image_data['focus_image'] = $img_focus_points[ $image_id ] ?? '';
            $attachment_fields = get_post_custom($image_id);
            $image_data['credits'] = '';
            if (isset($attachment_fields['_credit_text'][0]) && ! empty($attachment_fields['_credit_text'][0])) {
                $image_data['credits'] = $attachment_fields['_credit_text'][0];
                if (! is_numeric(strpos($attachment_fields['_credit_text'][0], '©'))) {
                    $image_data['credits'] = '© ' . $image_data['credits'];
                }
            }

            if (count((array) $image_data_array) >= 3) {
                $images_dimensions[] = $image_data_array[1];
                $images_dimensions[] = $image_data_array[2];
            }

            $images[] = $image_data;
        }

        return $images;
    }

    /**
     * Add all the necessary code and scripts to make the native lightbox feature work.
     *
     */
    public function add_lightbox_scripts(): void
    {
        if (!has_block('planet4-blocks/' . self::BLOCK_NAME)) {
            return;
        }

        $inter_js_path = includes_url('js/dist/') . 'interactivity.js';
        $img_view_path = includes_url('blocks/image/view.js');

        //phpcs:disable Generic.Files.LineLength.MaxExceeded 
        ?>
        <script type="importmap" id="wp-importmap">
            {"imports":{"@wordpress/interactivity":"<?php echo $inter_js_path; ?>"}}
        </script>
        <script type="module" src="<?php echo $img_view_path; ?>" id="@wordpress/block-library/image-js-module"></script>
        <link rel="modulepreload" href="<?php echo $inter_js_path; ?>" id="@wordpress/interactivity-js-modulepreload">

        <div class="wp-lightbox-overlay zoom"
             data-wp-interactive="core/image"
             data-wp-context='{}'
             data-wp-bind--role="state.roleAttribute"
             data-wp-bind--aria-label="state.currentImage.ariaLabel"
             data-wp-bind--aria-modal="state.ariaModal"
             data-wp-class--active="state.overlayEnabled"
             data-wp-class--show-closing-animation="state.showClosingAnimation"
             data-wp-watch="callbacks.setOverlayFocus"
             data-wp-on--keydown="actions.handleKeydown"
             data-wp-on-async--touchstart="actions.handleTouchStart"
             data-wp-on--touchmove="actions.handleTouchMove"
             data-wp-on-async--touchend="actions.handleTouchEnd"
             data-wp-on-async--click="actions.hideLightbox"
             data-wp-on-async-window--resize="callbacks.setOverlayStyles"
             data-wp-on-async-window--scroll="actions.handleScroll"
             tabindex="-1">
            
            <button type="button" aria-label="Close" style="fill: var(--body--color)" class="close-button">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="20" height="20" aria-hidden="true" focusable="false">
                    <path d="M13 11.8l6.1-6.3-1-1-6.1 6.2-6.1-6.2-1 1 6.1 6.3-6.5 6.7 1 1 6.5-6.6 6.5 6.6 1-1z"></path>
                </svg>
            </button>
            
            <div class="lightbox-image-container">
                <figure data-wp-bind--class="state.currentImage.figureClassNames" data-wp-bind--style="state.currentImage.figureStyles">
                    <img data-wp-bind--alt="state.currentImage.alt" data-wp-bind--class="state.currentImage.imgClassNames" data-wp-bind--style="state.imgStyles" data-wp-bind--src="state.currentImage.currentSrc">
                </figure>
            </div>
            
            <div class="lightbox-image-container">
                <figure data-wp-bind--class="state.currentImage.figureClassNames" data-wp-bind--style="state.currentImage.figureStyles">
                    <img data-wp-bind--alt="state.currentImage.alt" data-wp-bind--class="state.currentImage.imgClassNames" data-wp-bind--style="state.imgStyles" data-wp-bind--src="state.enlargedSrc">
                </figure>
                <div class="gallery-caption"></div>
            </div>
            
            <div class="scrim" style="background-color: var(--body--background-color)" aria-hidden="true"></div>
            <style data-wp-text="state.overlayStyles"></style>
        </div>
        <?php
        //phpcs:enable Generic.Files.LineLength.MaxExceeded
    }
}
