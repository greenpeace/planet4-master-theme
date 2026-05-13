<?php

/**
 * SocialMedia block class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

use WP_REST_Server;

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

    private const FB_API_BASE_URL = 'https://graph.facebook.com/v9.0';
    private const FB_PAGE_OEMBED = self::FB_API_BASE_URL . '/oembed_page';
    private const FB_POST_OEMBED = self::FB_API_BASE_URL . '/oembed_post';
    private const FB_VIDEO_OEMBED = self::FB_API_BASE_URL . '/oembed_video';
    private const INSTAGRAM_OEMBED = self::FB_API_BASE_URL . '/instagram_oembed';
    private const FB_CACHE_TTL = 3600; // Time in seconds to cache the response of an FB api call.
    private const FB_CALL_TIMEOUT = 10; // Seconds after which the api call will timeout if not responded.


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
                        'default' => 'oembed',
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
        $url = $fields['social_media_url'] ?? '';
        $embed_type = $fields['embed_type'];
        $alignment_class = $fields['alignment_class'];
        $facebook_page_tab = $fields['facebook_page_tab'];

        $data = [
            'title' => $title,
            'description' => $description,
            'alignment_class' => $alignment_class,
        ];

        if ($url) {
            if ('oembed' === $embed_type) {
                // need to remove . so instagr.am becomes instagram.
                // $provider = preg_replace('#(^www\.)|(\.com$)|(\.)#', '', strtolower(wp_parse_url($url, PHP_URL_HOST)));
                // if (in_array($provider, self::ALLOWED_OEMBED_PROVIDERS, true)) {
                //     $data['embed_code'] = $this->get_fb_oembed_html(rawurlencode($url), $provider);
                // }
            } elseif ('facebook_page' === $embed_type) {
                $data['facebook_page_url'] = $url;
                $data['facebook_page_tab'] = $facebook_page_tab;
            }
        }

        return $data;
    }
}
// phpcs:enable Generic.Files.LineLength.MaxExceeded
