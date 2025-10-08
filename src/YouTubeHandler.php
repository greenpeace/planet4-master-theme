<?php

namespace P4\MasterTheme;

/**
 * Class YouTubeHandler
 */
class YouTubeHandler
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_filter('embed_oembed_html', [$this, 'filter_youtube_oembed_nocookie'], 10, 2);
        add_filter('oembed_result', [$this, 'filter_youtube_oembed_nocookie'], 10, 2);
    }

    /**
     * Filter function for embed_oembed_html.
     * Transform youtube embeds to youtube-nocookie.
     *
     * @see https://developer.wordpress.org/reference/hooks/embed_oembed_html/
     *
     * @param mixed  $cache The cached HTML result, stored in post meta.
     * @param string $url The attempted embed URL.
     *
     * @return mixed
     */
    public function filter_youtube_oembed_nocookie($cache, string $url)
    {
        return $this->youtube_filter($cache, $url);
    }

    /**
     * Filter function for embed_oembed_html.
     * Transform youtube embeds to youtube-nocookie.
     *
     * @see https://developer.wordpress.org/reference/hooks/embed_oembed_html/
     *
     * @param mixed  $cache The cached HTML result, stored in post meta.
     * @param string $url The attempted embed URL.
     *
     * @return mixed
     */
    private function youtube_filter($cache, string $url)
    {
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return $cache;
        }

        if (!empty($url)) {
            if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                [$youtube_id, $query_string] = self::parse_youtube_url($url);

                $style = "background-image: url('https://i.ytimg.com/vi/$youtube_id/hqdefault.jpg');";

                return '<lite-youtube style="' . $style . '" videoid="' . $youtube_id
                    . '" params="' . $query_string . '"></lite-youtube>';
            }
        }

        return $cache;
    }

    /**
     * Parse info out of a Youtube URL.
     *
     * @param string $url The embedded url.
     *
     * @return string[] The youtube ID and the query string.
     */
    private static function parse_youtube_url(string $url): ?array
    {
        // @see https://stackoverflow.com/questions/3392993/php-regex-to-get-youtube-video-id
        // phpcs:ignore Generic.Files.LineLength.MaxExceeded
        $re = "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user|shorts)\/))([^\?&\"'>]+)/";
        preg_match_all($re, $url, $matches, PREG_SET_ORDER);
        $youtube_id = $matches[0][1] ?? null;

        // For now just rel, but we can extract more from the url.
        $query_string = apply_filters('planet4_youtube_embed_parameters', 'rel=0');

        return [$youtube_id, $query_string];
    }
}
