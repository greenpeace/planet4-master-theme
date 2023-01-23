<?php

namespace P4\MasterTheme;

/**
 * Class HttpHeaders
 */
class HttpHeaders
{
    /**
     * Headers constructor.
     */
    public function __construct()
    {
        add_action('wp_headers', [ $this, 'send_content_security_policy_header' ], 10, 1);
    }

    /**
     * Send Content Security Policy (CSP) HTTP headers.
     *
     * @param string[] $headers Associative array of headers to be sent.
     */
    public function send_content_security_policy_header($headers): array
    {
        $default_allowed_frame_ancestors = [ '\'self\'' ];

        /**
         * Filter hook to add trusted frame ancestors to the Content Security Policy.
         *
         * @param array $additional_allowed_frame_ancestors Array of domains to whitelist as frame ancestors.
         */
        $additional_allowed_frame_ancestors = apply_filters('planet4_csp_allowed_frame_ancestors', []);

        if (is_array($additional_allowed_frame_ancestors)) {
            $allowed_frame_ancestors = array_merge($default_allowed_frame_ancestors, $additional_allowed_frame_ancestors);
        }

        $directives = [
            'default-src * \'self\' data: \'unsafe-inline\' \'unsafe-eval\'',
            'frame-ancestors ' . implode(' ', $allowed_frame_ancestors),
        ];

        $csp_header = implode('; ', $directives);
        $csp_header = preg_replace("/\r|\n/", '', $csp_header);

        $headers['Content-Security-Policy'] = $csp_header;

        // In addition, send the "X-Frame-Options" header when no other trusted frame ancestors were added through the filter.
        if ($allowed_frame_ancestors === $default_allowed_frame_ancestors) {
            $headers['X-Frame-Options'] = 'SAMEORIGIN';
        }

        return $headers;
    }
}
