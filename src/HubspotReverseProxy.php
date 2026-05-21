<?php

namespace P4\MasterTheme;

use WP;

/**
 * Class HubspotReverseProxy
 *
 * bla bla bla
 *
 */
class HubspotReverseProxy
{
    /**
     * Constructor
     */
    public function __construct()
    {
        add_action( 'parse_request', [$this, 'p4_hubspot_reverse_proxy'] );
    }

    public function p4_hubspot_reverse_proxy( WP $wp ): void {

        $target_url = $this->get_config_data($wp);

        if (!$target_url) {
            return;
        }

        $content = $this->remote_get_content($target_url);

        if ( !$content ) {
            return;
        }

        if ( str_contains( $content['type'], 'text/html' ) ) {
            $content['body'] = $this->inject_canonical( $content['body'], $target_url );
        }

        echo $content['body']; // phpcs:ignore WordPress.Security.EscapeOutput
        exit;
    }

    private function get_config_data($wp)
    {
        $options = get_option( 'planet4_options', [] );

        if ( empty( $options['hubspot_reverse_proxy'] ) ) {
            return null;
        }

        $p4_path        = trim( $options['hubspot_reverse_proxy_p4_path'] ?? '', '/' );
        $hubspot_domain = rtrim( $options['hubspot_reverse_proxy_domain'] ?? '', '/' );
        $hubspot_path   = trim( $options['hubspot_reverse_proxy_hubspot_path'] ?? '', '/' );

        if ( ! $p4_path || ! $hubspot_domain || ! $hubspot_path ) {
            return null;
        }

        // Ensure the domain includes a scheme.
        if ( ! preg_match( '#^https?://#', $hubspot_domain ) ) {
            $hubspot_domain = 'https://' . $hubspot_domain;
        }

        $request_path = trim( $wp->request, '/' );

        if ( $request_path !== $p4_path && ! str_starts_with( $request_path, $p4_path . '/' ) ) {
            return null;
        }

        $sub_path   = substr( $request_path, strlen( $p4_path ) );
        $target_url = $hubspot_domain . '/' . $hubspot_path . $sub_path;

        return $target_url;
    }

    private function remote_get_content($target_url)
    {
        $query_string = $_SERVER['QUERY_STRING'] ?? '';
        if ( $query_string ) {
            $target_url .= '?' . $query_string;
        }

        $forward_headers = [];
        foreach ( [ 'Accept', 'Accept-Language', 'Accept-Encoding', 'Cookie' ] as $h ) {
            $key = 'HTTP_' . strtoupper( str_replace( '-', '_', $h ) );
            if ( ! empty( $_SERVER[ $key ] ) ) {
                $forward_headers[ $h ] = $_SERVER[ $key ];
            }
        }

        $response = wp_remote_get( $target_url, [
            'timeout'     => 15,
            'redirection' => 5,
            'headers'     => $forward_headers,
            'user-agent'  => 'Planet4-ReverseProxy/1.0 (+https://planet4.greenpeace.org)',
            'sslverify'   => true,
        ] );

        if ( is_wp_error( $response ) ) {
            return null;
        }

        $status_code  = wp_remote_retrieve_response_code( $response );
        $content_type = wp_remote_retrieve_header( $response, 'content-type' );
        $body         = wp_remote_retrieve_body( $response );

        http_response_code( (int) $status_code );

        header( 'Content-Type: ' . $content_type );
        header( 'X-Proxied-By: Planet4' );
        header_remove( 'Link' );

        return [
            'body' => $body,
            'type' => $content_type,
        ];
    }

    /**
     * Injects a <link rel="canonical"> pointing at the original HubSpot URL.
     * If a canonical already exists (HubSpot sometimes adds one), replace it.
     */
    private function inject_canonical( string $html, string $canonical_url ): string {
        $tag = sprintf( '<link rel="canonical" href="%s">', esc_url( $canonical_url ) );

        // Replace an existing canonical if present.
        if ( preg_match( '/<link[^>]+rel=["\']canonical["\'][^>]*>/i', $html ) ) {
            return preg_replace( '/<link[^>]+rel=["\']canonical["\'][^>]*>/i', $tag, $html );
        }

        // Otherwise inject just before </head>.
        return str_ireplace( '</head>', $tag . "\n</head>", $html );
    }
}
