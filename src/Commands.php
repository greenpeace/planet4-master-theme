<?php

namespace P4\MasterTheme;

use WP_CLI;

class Commands {
	public static function load() {
		if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
			return;
		}
		$activator_command = static function ( $args, $assoc_args ) {
			Activator::run();
		};
		WP_CLI::add_command( 'p4-run-activator', $activator_command );

		/**
		 * Put the CF API key into the options table, where the CF plugin uses it from.
		 */
		$put_cf_key_in_db = static function ( $args ) {
			$hostname = $args[0];
			if ( empty( $hostname ) ) {
				WP_CLI::error( 'Please specify the hostname.' );
			}

			if ( ! defined( 'CLOUDFLARE_API_KEY' ) || empty( CLOUDFLARE_API_KEY ) ) {
				WP_CLI::error( 'CLOUDFLARE_API_KEY constant is not set.' );
			}

			$domain_parts = explode( '.', $hostname );

			$root_domain = implode( '.', array_slice( $domain_parts, - 2 ) );
			update_option( 'cloudflare_api_key', CLOUDFLARE_API_KEY );
			update_option( 'automatic_platform_optimization', [ 'value' => 1 ] );
			update_option( 'cloudflare_cached_domain_name', $root_domain );
		};
		WP_CLI::add_command( 'p4-cf-key-in-db', $put_cf_key_in_db );
	}
}
