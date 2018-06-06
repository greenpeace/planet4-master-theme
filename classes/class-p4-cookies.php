<?php

if ( ! class_exists( 'P4_Cookies' ) ) {

	/**
	 * Class P4_Cookies
	 *
	 * @since 1.9
	 */
	class P4_Cookies {

		const COOKIE_NAME = 'greenpeace';

		/** The text that will replace blocked content.
		 *
		 * @var string
		 */
		private $filtered_text;

		/**
		 * P4_Cookies constructor.
		 */
		public function __construct() {
			$this->hooks();
		}

		/**
		 * Register actions for WordPress hooks and filters.
		 */
		private function hooks() {
			$options                = get_option( 'planet4_options' );
			$enforce_cookies_policy = isset( $options['enforce_cookies_policy'] ) ? true : false;

			// Do not add any hook if enforce cookies setting is not set.
			if ( false === $enforce_cookies_policy ) {
				return;
			}
			// If our cookie is not set then register the following filters.
			if ( '2' !== $this->read_cookie( self::COOKIE_NAME ) ) {

				add_filter( 'gal_set_login_cookie', [ $this, 'filter_gal_set_login_cookie', 10, 1 ] );
				add_filter( 'embed_oembed_html', [ $this, 'filter_embed' ], 10, 3 );
				add_filter( 'the_content', [ $this, 'filter_iframes_and_embeds' ] );
			}
		}


		/**
		 * Filter action for embed_oembed_html hook. Remove embeds from restricted providers.
		 *
		 * @param string $html The cached HTML result.
		 * @param string $url  The attempted embed URL.
		 * @param array  $attr An array of shortcode attributes.
		 *
		 * @since 1.9
		 *
		 * @return string
		 */
		public function filter_embed( $html, $url, $attr ) {
			$allowed    = true;
			$dissalowed = $this->get_providers_data();
			foreach ( $dissalowed as $provider ) {
				foreach ( $provider['urls'] as $purl ) {
					if ( false !== stristr( $url, $purl ) ) {
						$allowed = false;
					}
				}
			}
			if ( $allowed ) {
				return $html;
			}

			return '<div class="cookies-filtered-content">' .
					__( 'This content is filtered out because of cookies policy.', 'planet4-master-theme' ) . '</div>';
		}

		/**
		 * Filter setting google login cookie.
		 *
		 * @param bool $dosetcookie Whether to set the cookie or not.
		 *
		 * @since 1.9
		 *
		 * @return bool
		 */
		public function filter_google_login_set_login_cookie( $dosetcookie ) {
			global $pagenow;

			return 'wp-login.php' === $pagenow;
		}


		/**
		 * Get an entry from $_COOKIE super global
		 *
		 * @param string $name Cookie name.
		 *
		 * @since 1.9
		 *
		 * @return bool Return false if entry does not exist, otherwise return cookie value.
		 */
		public function read_cookie( $name = '' ) {
			if ( isset( $_COOKIE[ $name ] ) ) {
				return $_COOKIE[ $name ];
			} else {
				return false;
			}
		}


		/**
		 * Filter action for the_content hook. Remove iframes/embeds from the post content.
		 *
		 * @param string $content The post content.
		 *
		 * @since 1.9
		 *
		 * @return mixed
		 */
		public function filter_iframes_and_embeds( $content ) {

			// Regex for iframes and embeds inside content.
			$pattern = '~<embed.*>[^>]*</embed>|<iframe.*>[^>]*</iframe>~';

			preg_match_all( $pattern, $content, $matches );

			foreach ( $matches[0] as $match ) {
				$replacement = '<div class="cookies-filtered-content">' .
							  __( 'This content is filtered out because of cookies policy.', 'planet4-master-theme' ) . '</div>';

				// Replace match.
				$content = str_replace( $match, $replacement, $content );
			}

			return $content;
		}


		/**
		 * Define a set of content providers for which content will be blocked.
		 *
		 * @return array
		 */
		private function get_providers_data() {
			return [
				'youtube'     => [
					'name' => 'youtube',
					'urls' =>
						[
							'youtu.be',
							'youtube.com',
						],
				],
				'googledocs'  => [
					'name' => 'googledocs',
					'urls' =>
						[
							'docs.google.com',
						],
				],
				'googledrive' => [
					'name' => 'googledrive',
					'urls' =>
						[
							'drive.google.com',
						],
				],
			];
		}

	}
}
