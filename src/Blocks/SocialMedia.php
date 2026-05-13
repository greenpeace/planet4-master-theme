<?php

/**
 * SocialMedia block class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

/**
 * Class SocialMedia
 * @package P4\MasterTheme\Blocks
 */
class SocialMedia extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'social-media';

    private const ALLOWED_OEMBED_PROVIDERS = [
        'facebook',
        'instagram',
    ];

    /**
     * SocialMedia constructor.
     */
    public function __construct()
    {
        $this->register_socialmedia_block();
    }

    /**
     * Register block
     */
    public function register_socialmedia_block(): void
    {
        // - Register the block for the editor
        register_block_type(
            self::get_full_block_name(),
            [
                'api_version' => 3,
                'editor_script' => 'planet4-blocks-theme-editor-script',
                'render_callback' => static function ($attributes, $content) {
                    if ('' !== trim($content)) {
                        return $content;
                    }

                    return ( new SocialMedia() )->render($attributes);
                },
                'attributes' => [
                    'title' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'description' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'embed_type' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'facebook_page_tab' => [
                        'type' => 'string',
                        'default' => 'timeline',
                    ],
                    'social_media_url' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'alignment_class' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                    'embed_code' => [
                        'type' => 'string',
                        'default' => '',
                    ],
                ],
            ]
        );

        add_action('enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ]);
        add_action('wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ]);
    }

    /**
     * Frontend script
     */
    public static function enqueue_frontend_script(): void
    {
        wp_enqueue_script(
            'instagram-embed',
            'https://www.instagram.com/embed.js',
            [],
            null,
            true
        );
    }

    /**
     * Required by the `BaseBlock` class.
     *
     * @param array $fields Unused, required by the abstract function.
     *
     * @return array Array.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public function prepare_data(array $fields): array
    {
        $title = $fields['title'] ?? '';
        $description = $fields['description'] ?? '';
        $alignment_class = $fields['alignment_class'];

        $data = [
            'title' => $title,
            'description' => $description,
            'alignment_class' => $alignment_class,
        ];

        return $data;
    }
}
// phpcs:enable Generic.Files.LineLength.MaxExceeded
