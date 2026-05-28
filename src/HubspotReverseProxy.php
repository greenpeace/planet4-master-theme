<?php

namespace P4\MasterTheme;

use WP;

/**
 * Class HubspotReverseProxy
 *
 * Proxies requests matching a configured P4 path to a corresponding HubSpot URL.
 */
class HubspotReverseProxy
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action('parse_request', [$this, 'p4_hubspot_reverse_proxy']);
    }

    /**
     * Handles the reverse proxy logic on every WordPress request.
     *
     * Security notice — trust assumption:
     * The response body is echoed without escaping (`echo $content['body']`), which is
     * fundamentally what a reverse proxy does. However, this means any XSS present on
     * the HubSpot side executes under the P4 origin and has full access to P4 cookies
     * and localStorage.
     *
     * @param WP $wp Current WordPress environment instance.
     */
    public function p4_hubspot_reverse_proxy(WP $wp): void
    {
        $target_data = $this->get_config_data($wp);

        if (!$target_data) {
            return;
        }

        $content = $this->remote_get_content($target_data['target_url']);

        if (!$content) {
            return;
        }

        if (str_contains($content['type'], 'text/html')) {
            $content['body'] = $this->inject_canonical($content['body'], $target_data['target_url']);
            $content['body'] = $this->rewrite_relative_urls($content['body'], $target_data['hubspot_domain']);
        }

        echo $content['body']; // phpcs:ignore WordPress.Security.EscapeOutput
        exit;
    }

    /**
     * Resolves the target HubSpot URL for the current request based on Planet4 options.
     *
     * @param WP $wp Current WordPress environment instance.
     * @return array|null The target URL and Hubspot domain, or null if the request should not be proxied.
     */
    private function get_config_data(WP $wp): ?array
    {
        $options = get_option('planet4_options', []);

        if (empty($options['hubspot_reverse_proxy'])) {
            return null;
        }

        $p4_path = trim($options['hubspot_reverse_proxy_p4_path'] ?? '', '/');
        $hubspot_domain = rtrim($options['hubspot_reverse_proxy_domain'] ?? '', '/');
        $hubspot_path = trim($options['hubspot_reverse_proxy_hubspot_path'] ?? '', '/');

        if (!$p4_path || !$hubspot_domain) {
            return null;
        }

        // Ensure the domain includes a scheme.
        if (! preg_match('#^https?://#', $hubspot_domain)) {
            $hubspot_domain = 'https://' . $hubspot_domain;
        }

        $request_path = trim($wp->request, '/');

        if ($request_path !== $p4_path && ! str_starts_with($request_path, $p4_path . '/')) {
            return null;
        }

        $sub_path = substr($request_path, strlen($p4_path));
        $target_url = $hubspot_domain . '/' . $hubspot_path . $sub_path;

        return [
            'hubspot_domain' => $hubspot_domain,
            'target_url' => $target_url,
        ];
    }

    /**
     * Fetches the content of the target URL via an HTTP GET request.
     *
     * @param string $target_url The fully-qualified URL to fetch.
     * @return array|null Associative array with 'body' and 'type' keys, or null on request failure.
     */
    private function remote_get_content(string $target_url): ?array
    {
        $query_string = $_SERVER['QUERY_STRING'] ?? '';

        if ($query_string) {
            $target_url .= '?' . $query_string;
        }

        $forward_headers = [];

        foreach ([ 'Accept', 'Accept-Language', 'Accept-Encoding', 'Cookie' ] as $h) {
            $key = 'HTTP_' . strtoupper(str_replace('-', '_', $h));
            if (empty($_SERVER[$key])) {
                continue;
            }

            $forward_headers[$h] = $_SERVER[$key];
        }

        $response = wp_remote_get($target_url, [
            'timeout' => 15,
            'redirection' => 5,
            'headers' => $forward_headers,
            'sslverify' => true,
        ]);

        if (is_wp_error($response)) {
            return null;
        }

        $status_code = wp_remote_retrieve_response_code($response);
        $content_type = wp_remote_retrieve_header($response, 'content-type');
        $body = wp_remote_retrieve_body($response);

        http_response_code((int) $status_code);

        header('Content-Type: ' . $content_type);
        header('X-Proxied-By: Planet4');
        header_remove('Link');

        return [
            'body' => $body,
            'type' => $content_type,
        ];
    }

    /**
     * Rewrites root-relative URLs in proxied HTML to point to the HubSpot domain.
     * Both single- and double-quoted attribute values are supported.
     * The regex matches the following patterns where the value starts with '/':
     *  - src="..."        e.g. src="/images/logo.png"
     *  - href="..."       e.g. href="/about"
     *  - action="..."     e.g. action="/submit"
     *  - srcset="..."     e.g. srcset="/img.png 1x, /img@2x.png 2x"
     *  - data-*="..."     e.g. data-src="/lazy.jpg", data-bg-url="/hero.jpg"
     *  - style="..."      e.g. style="background: url(/images/bg.jpg)"
     *
     * @param string $html            The raw HTML returned from the HubSpot proxy.
     * @param string $hubspot_domain  The absolute origin to prepend.
     *
     * @return string The HTML with all root-relative URLs rewritten to absolute HubSpot URLs.
     */
    private function rewrite_relative_urls(string $html, string $hubspot_domain): string
    {
        return preg_replace_callback(
            '/(\b(?:src|href|action|srcset|data-[\w\-]+)=["\'])(\\/(?!\\/)[^"\']*["\'])|(\\bstyle=["\'])([^"\']*url\\(\\/(?!\\/)[^)]*\\)[^"\']*["\'])/',
            function (array $matches) use ($hubspot_domain): string {
                if ($matches[1]) {
                    return $matches[1] . $hubspot_domain . $matches[2];
                }
                return $matches[3] . preg_replace(
                    '/url\\((\\/(?!\\/)[^)]*\\))/',
                    'url(' . $hubspot_domain . '$1)',
                    $matches[4]
                );
            },
            $html
        );
    }

    /**
     * Injects a <link rel="canonical"> pointing at the original HubSpot URL.
     *
     * @param string $html The HTML content to modify.
     * @param string $canonical_url The canonical URL to set.
     * @return string The modified HTML with the canonical tag injected or replaced.
     */
    private function inject_canonical(string $html, string $canonical_url): string
    {
        $tag = sprintf('<link rel="canonical" href="%s">', esc_url($canonical_url));

        // Replace an existing canonical if present.
        if (preg_match('/<link[^>]+rel=["\']canonical["\'][^>]*>/i', $html)) {
            return preg_replace('/<link[^>]+rel=["\']canonical["\'][^>]*>/i', $tag, $html);
        }

        // Otherwise inject just before </head>.
        return str_ireplace('</head>', $tag . "\n</head>", $html);
    }
}
