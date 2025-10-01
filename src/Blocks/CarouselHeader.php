<?php

/**
 * Carousel Header block class
 *
 * @package P4\MasterTheme
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

/**
 * Class CarouselHeader
 * Registers the CarouselHeader block.
 *
 * @package P4\MasterTheme\Blocks
 */
class CarouselHeader extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'carousel-header';

    /**
     * CarouselHeader constructor.
     */
    public function __construct()
    {
        $this->register_carouselheader_block();
    }

    /**
     * Register CarouselHeader block.
     */
    public function register_carouselheader_block(): void
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'render_callback' => [ $this, 'front_end_rendered_fallback' ],
                'attributes' => [
                    'carousel_autoplay' => [
                        'type' => 'boolean',
                        'default' => false,
                    ],
                    'slides' => [
                        'type' => 'array',
                        'default' => [],
                        'items' => [
                            'type' => 'object',
                            // In JSON Schema you can specify object properties in the properties attribute.
                            'properties' => [
                                'image' => [
                                    'type' => 'integer',
                                ],
                                'image_url' => [
                                    'type' => 'string',
                                ],
                                'image_srcset' => [
                                    'type' => 'string',
                                ],
                                'image_alt' => [
                                    'type' => 'string',
                                ],
                                'header' => [
                                    'type' => 'string',
                                ],
                                'description' => [
                                    'type' => 'string',
                                ],
                                'link_text' => [
                                    'type' => 'string',
                                ],
                                'link_url' => [
                                    'type' => 'string',
                                ],
                                'focal_points' => [
                                    'type' => 'object',
                                ],
                                'link_url_new_tab' => [
                                    'type' => 'boolean',
                                ],
                            ],
                        ],
                    ],
                    'currentImageIndex' => [
                        'type' => 'integer',
                        'default' => 0,
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

    /**
     * Load additional frontend assets
     */
    public static function enqueue_frontend_assets(): void
    {
        parent::enqueue_frontend_assets();
    }

    /**
     * If the content is not empty, it's the new version and doesn't need any back end rendering.
     * Otherwise, it means the block was not migrated in the editor yet. Fall back to front end rendering from scratch.
     *
     * @param array  $attributes Attributes of the block.
     * @param string $content Content of the block.
     *
     * @return string The block's content string.
     */
    public function front_end_rendered_fallback(array $attributes, string $content): string
    {
        if (! empty($content)) {
            return $content;
        }

        if (! empty($attributes['slides']) && empty($attributes['slides'][0]['image_url'])) {
            $attributes['slides'] = self::get_slides_image_data($attributes['slides']);
        }

        $json = wp_json_encode([ 'attributes' => $attributes ]);

        // Render the block using a regular front end rendered, until the block data was migrated in the editor. For
        // production sites we will do this for all occurrences of the block, right after deploy.
        return '<div data-render="planet4-blocks/carousel-header" data-attributes="' . htmlspecialchars($json) . '">'
        . '</div>';
    }

    /**
     * Get image data for the slides.
     *
     * @param array $slides Slides of this block.
     *
     * @return array The image data to be passed in the View.
     */
    private static function get_slides_image_data(array $slides): array
    {
        if (! empty($slides)) {
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
        }

        return $slides;
    }
}
