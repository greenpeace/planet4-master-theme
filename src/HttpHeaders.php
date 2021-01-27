<?php

namespace P4\MasterTheme;

/**
 * Class HttpHeaders
 */
class HttpHeaders {
	/**
	 * Headers constructor.
	 */
	public function __construct() {
		add_action( 'wp_headers', [ $this, 'send_content_security_policy_header' ], 10, 1 );
	}


	/**
	 * Send Content Security Policy (CSP) HTTP headers.
	 */
	public function send_content_security_policy_header() {
		$default_allowed_frame_ancestors = [ '\'self\'' ];

		// Filter hook to allow adding trusted frame ancestors.
		$additional_allowed_frame_ancestors = apply_filters( 'planet4_csp_allowed_frame_ancestors', [] );

		$allowed_frame_ancestors = array_merge( $default_allowed_frame_ancestors, (array) $additional_allowed_frame_ancestors );

		$directives = [
			'default-src *',
			'frame-ancestors ' . implode( ' ', $allowed_frame_ancestors ),
		];

		$csp_header = 'Content-Security-Policy: ' . implode( '; ', $directives );
		$csp_header = preg_replace( "/\r|\n/", '', $csp_header );

		header( $csp_header );

		// In addition, send the "X-Frame-Options" header when no other trusted frame ancestors were added through the filter.
		if ( $allowed_frame_ancestors === $default_allowed_frame_ancestors ) {
			header( 'X-Frame-Options: SAMEORIGIN' );
		}
	}
}
