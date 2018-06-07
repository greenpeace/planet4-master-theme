<?php

if ( ! class_exists( 'P4_Cookies' ) ) {

	/**
	 * Class P4_Cookies
	 *
	 * @since 1.9
	 */
	class P4_Cookies {

		const COOKIE_NAME = 'greenpeace';

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
			}
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
	}
}
