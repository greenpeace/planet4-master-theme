<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Features\LazyYoutubePlayer;

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
     * Returns the whitelist of URL params that require keeping the normal youtube.com domain.
     *
     */
    protected function get_keep_params(): array
    {
        return apply_filters('planet4_youtube_keep_domain_params', ['list', 'si', 'rel', 'start', 't', 'index']);
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
        if (LazyYoutubePlayer::is_active()) {
            return $this->new_youtube_filter($cache, $url);
        }

        return $this->old_youtube_filter($cache, $url);
    }

    /**
     * Filter function for embed_oembed_html.
     * Transform youtube embeds to youtube-nocookie if optional keep params are absent.
     *
     * @see https://developer.wordpress.org/reference/hooks/embed_oembed_html/
     *
     * @param mixed  $cache The cached HTML result, stored in post meta.
     * @param string $url The attempted embed URL.
     *
     * @return mixed
     */
    private function new_youtube_filter($cache, string $url)
    {
        if (is_admin() || (defined('REST_REQUEST') && REST_REQUEST)) {
            return $cache;
        }

        if (empty($url) || (strpos($url, 'youtube.com') === false && strpos($url, 'youtu.be') === false)) {
            return $cache;
        }

        // If the original URL contains a keep param, don't convert to lite
        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $url_query_params);
        $keep_params = $this->get_keep_params();
        foreach ($keep_params as $p) {
            if (array_key_exists($p, $url_query_params)) {
                return $cache; // keep the oEmbed iframe as it is (so it can show custom related videos)
            }
        }

        // otherwise safely make the lite player and preserve allowed params
        [$youtube_id, $query_string] = self::parse_youtube_url($url);

        $style = "background-image: url('https://i.ytimg.com/vi/$youtube_id/hqdefault.jpg');";

        return '<lite-youtube style="' . esc_attr($style) . '" videoid="' . esc_attr($youtube_id)
            . '" params="' . esc_attr($query_string) . '"></lite-youtube>';
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
    private function old_youtube_filter($cache, string $url)
    {
        if (empty($url) || (strpos($url, 'youtube.com') === false && strpos($url, 'youtu.be') === false)) {
            return $cache;
        }

        parse_str(parse_url($url, PHP_URL_QUERY) ?? '', $url_query_params);

        $keep_params = $this->get_keep_params();

        libxml_use_internal_errors(true);
        $dom = new DOMDocument();
        // phpcs:ignore Generic.Files.LineLength.MaxExceeded
        $dom->loadHTML(mb_convert_encoding($cache, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        $iframes = $dom->getElementsByTagName('iframe');

        foreach ($iframes as $iframe) {
            $src = $iframe->getAttribute('src');
            if (empty($src)) {
                continue;
            }

            $parts = parse_url($src);
            parse_str($parts['query'] ?? '', $iframe_query_params);
            $merged_params = array_merge($url_query_params, $iframe_query_params);

            $keep_domain = false;
            foreach ($keep_params as $p) {
                if (array_key_exists($p, $merged_params) && $merged_params[$p] !== '') {
                    $keep_domain = true;
                    break;
                }
            }

            if ($keep_domain) {
                $host = $parts['host'] ?? '';
                if (strpos($host, 'youtube-nocookie.com') !== false) {
                    $parts['host'] = 'www.youtube.com';
                    $new_src = $this->build_url($parts);
                    $iframe->setAttribute('src', $new_src);
                }
            } else {
                $iframe_query_params = $iframe_query_params ?? [];
                if (!isset($iframe_query_params['rel'])) {
                    $iframe_query_params['rel'] = '0';
                }

                $parts['host'] = 'www.youtube-nocookie.com';
                $parts['query'] = http_build_query($iframe_query_params);
                $new_src = $this->build_url($parts);
                $iframe->setAttribute('src', $new_src);
            }
        }

        $new_cache = $dom->saveHTML();
        libxml_clear_errors();

        // If DOM parsing produced nothing, fall back to the old string replace
        if (empty(trim($new_cache))) {
            $replacements = [
                'youtube.com' => 'youtube-nocookie.com',
                'feature=oembed' => 'feature=oembed&rel=0',
            ];

            return str_replace(array_keys($replacements), array_values($replacements), $cache);
        }

        return $new_cache;
    }

     /**
     * Rebuild URL from parse_url parts.
     */
    private function build_url(array $parts): string
    {
        $scheme = isset($parts['scheme']) ? $parts['scheme'] . '://' : 'https://';
        $host = $parts['host'] ?? '';
        $port = isset($parts['port']) ? ':' . $parts['port'] : '';
        $path = $parts['path'] ?? '';
        $query = isset($parts['query']) && $parts['query'] !== '' ? '?' . $parts['query'] : '';
        $frag = isset($parts['fragment']) ? '#' . $parts['fragment'] : '';

        return $scheme . $host . $port . $path . $query . $frag;
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
