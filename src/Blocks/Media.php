<?php

/**
 * Media block class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4\MasterTheme\Blocks;

/**
 * Class Media
 * @package P4\MasterTheme\Blocks
 */
class Media extends BaseBlock
{
    /**
     * Block name.
     *
     * @const string BLOCK_NAME.
     */
    public const BLOCK_NAME = 'media-video';

    /**
     * Media constructor.
     */
    public function __construct()
    {
        register_block_type(
            self::get_full_block_name(),
            [
                'editor_script' => 'planet4-blocks-theme-editor-script',
                'render_callback' => static function ($attributes, $content) {
                    if ('' !== trim($content)) {
                        return $content;
                    }

                    $attributes = array_merge($attributes, Media::get_media_data($attributes));

                    if (isset($attributes['youtube_id'])) {
                        $attributes['media_url'] = $attributes['youtube_id'];
                        unset($attributes['youtube_id']);
                    }

                    return self::render_frontend($attributes);
                },
                'attributes' => [
                    'video_title' => [
                        'type' => 'string',
                    ],
                    'description' => [
                        'type' => 'string',
                    ],
                    'media_url' => [
                        'type' => 'string',
                    ],
                    'video_poster_img' => [
                        'type' => 'integer',
                    ],
                    'embed_html' => [
                        'type' => 'string',
                    ],
                    'poster_url' => [
                        'type' => 'string',
                    ],
                ],
            ]
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
        return [];
    }

    /**
     * Get Media Data
     *
     * @param array $fields Fields passed from the frontend.
     * @return array The data to be passed to the frontend block.
     */
    public static function get_media_data(array $fields): array
    {
        $media_url = $fields['media_url'] ?? $fields['youtube_id'] ?? '';
        $url_path_segment = wp_parse_url($media_url, PHP_URL_PATH);
        $poster_url = '';

        // Assume that a non-URL is a YouTube video ID, for back compat.
        if (false === strstr($media_url, '/')) {
            $media_url = "https://www.youtube.com/watch?v={$media_url}";
        }

        if (preg_match('/^(https?)?:\/\/soundcloud.com\//i', $media_url)) {
            // Soundcloud track URL (differentiated for styling purposes).
            $embed_html = wp_oembed_get($media_url);
        } elseif (preg_match('/\.mp4$/', $url_path_segment)) {
            // Bare video URL.
            $poster = empty($fields['video_poster_img'])
                ? '' : wp_get_attachment_image_src($fields['video_poster_img'], 'large');

            $poster_url = $poster[0] ?? '';
            $embed_html = wp_video_shortcode(
                [
                    'src' => $media_url,
                    'poster' => $poster_url,
                ]
            );
        } elseif (preg_match('/\.(mp3|wav|ogg)$/', $url_path_segment)) {
            // Bare audio URL.
            $embed_html = wp_audio_shortcode([ 'src' => $media_url ]);
        } else {
            $embed_html = wp_oembed_get($media_url);
        }

        $data = [
            'embed_html' => $embed_html,
            'poster_url' => $poster_url,
        ];
        return $data;
    }
}
