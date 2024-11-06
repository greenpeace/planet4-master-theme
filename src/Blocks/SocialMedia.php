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
                $provider = preg_replace('#(^www\.)|(\.com$)|(\.)#', '', strtolower(wp_parse_url($url, PHP_URL_HOST)));
                if (in_array($provider, self::ALLOWED_OEMBED_PROVIDERS, true)) {
                    $data['embed_code'] = $this->get_fb_oembed_html(rawurlencode($url), $provider);
                }
            } elseif ('facebook_page' === $embed_type) {
                $data['facebook_page_url'] = $url;
                $data['facebook_page_tab'] = $facebook_page_tab;
            }
        }

        return $data;
    }

    /**
     * Gets Facebook, Instagram oembed html.
     *
     * @param String $url The facebook/Instagram post/page/video url.
     * @param String $provider The provider name such as facebook/instagram.
     *
     * @return String The oembed html or a message if something goes wrong.
     */
    public static function get_fb_oembed_html(string $url, string $provider): string
    {
        $from_cache = get_transient('fb_oembed_response_' . $url);
        if ($from_cache) {
            return $from_cache;
        }

        $fb_oembed_url = self::get_fb_oembed_url($url, $provider);

        // With the safe version of wp_safe_remote_{VERB) functions,
        // the URL is validated to avoid redirection and request forgery attacks.
        $response = wp_safe_remote_get(
            $fb_oembed_url,
            [
                'headers' => [
                    'Content-Type' => 'application/json; charset=UTF-8',
                ],
                'timeout' => self::FB_CALL_TIMEOUT,
            ]
        );

        $body = json_decode($response['body'], true);

        if (is_wp_error($response)) {
            return $response->get_error_message() . ' ' . $response->get_error_code();
        }

        // phpcs:disable Generic.Files.LineLength.MaxExceeded
        if (is_array($response) && \WP_Http::OK !== $response['response']['code']) {
            return $response['response']['message'] . ' ' . $response['response']['code'] . ' ' . $body['error']['message'];
        }

        set_transient('fb_oembed_response_' . $url, (string) $body['html'], self::FB_CACHE_TTL);

        return $body['html'];
    }

    /**
     * Construct & return facebook oembed API url.
     *
     * @param String $url The facebook/Instagram post/page/video url.
     * @param String $provider The provider name such as facebook/instagram.
     *
     * @return string A facebook oembed API url.
     */
    private static function get_fb_oembed_url(string $url, string $provider): string
    {
        $options = get_option('planet4_options');
        $fb_app_access_token = $options['fb_app_access_token'] ?? '';

        if ('instagram' === $provider) {
            $url = self::INSTAGRAM_OEMBED . '?url=' . $url . '&access_token=' . $fb_app_access_token;
        } elseif ('facebook' === $provider) {
            /**
             * Check if url is a facebook post, page or video.
             * Examples:
             *
             * Pages
             * https://www.facebook.com/{page-name}
             * https://www.facebook.com/{page-id}
             *
             * Posts
             * https://www.facebook.com/{page-name}/posts/{post-id}
             * https://www.facebook.com/{username}/posts/{post-id}
             * https://www.facebook.com/{username}/activity/{activity-id}
             * https://www.facebook.com/photo.php?fbid={photo-id}
             * https://www.facebook.com/photos/{photo-id}
             * https://www.facebook.com/permalink.php?story_fbid={post-id}&id={page-or-user-id}
             * https://www.facebook.com/media/set?set={set-id}
             * https://www.facebook.com/questions/{question-id}
             * https://www.facebook.com/notes/{username}/{note-url}/{note-id}
             *
             * Videos
             * https://www.facebook.com/{page-name}/videos/{video-id}/
             * https://www.facebook.com/{username}/videos/{video-id}/
             * https://www.facebook.com/video.php?id={video-id}
             * https://www.facebook.com/video.php?v={video-id}
             */
            if (preg_match('/(\/posts\/|\/activity\/|\/photo|\/permalink\.php|\/media\/|\/questions\/|\/notes\/)/', urldecode($url))) {
                $url = self::FB_POST_OEMBED . '?url=' . $url . '&access_token=' . $fb_app_access_token;
            } elseif (preg_match('/(\/videos\/|\/video\.php)/', urldecode($url))) {
                $url = self::FB_VIDEO_OEMBED . '?url=' . $url . '&access_token=' . $fb_app_access_token;
            } else {
                $url = self::FB_PAGE_OEMBED . '?url=' . $url . '&access_token=' . $fb_app_access_token;
            }
        }

        return $url;
    }
}
// phpcs:enable Generic.Files.LineLength.MaxExceeded
