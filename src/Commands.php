<?php
/**
 * Commands.
 */

namespace P4\MasterTheme;

use CF\Integration\DefaultConfig;
use CF\Integration\DefaultIntegration;
use CF\Integration\DefaultLogger;
use CF\WordPress\DataStore;
use CF\WordPress\WordPressAPI;
use CF\WordPress\WordPressClientAPI;
use WP_CLI;

/**
 * Class with a static function just because PHP can't autoload functions.
 */
class Commands {
	/**
	 * Add some WP_CLI commands if we're in CLI.
	 */
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
			$hostname = $args[0] ?? null;
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

		$purge_urls = static function ( $args, $assoc_args ) {
			if ( ! Features::is_active( Features::CLOUDFLARE_DEPLOY_PURGE ) ) {
				WP_CLI::warning( 'Purge on deploy is not enabled, not purging.' );

				return;
			}

			if ( ! defined( 'CLOUDFLARE_PLUGIN_DIR' ) ) {
				define( 'CLOUDFLARE_PLUGIN_DIR', WP_PLUGIN_DIR . '/cloudflare/' );
			}
			require_once CLOUDFLARE_PLUGIN_DIR . 'vendor/autoload.php';

			if ( isset( $assoc_args['urls'] ) ) {
				$urls = explode( ',', $assoc_args['urls'] );
			} elseif ( isset( $assoc_args['all'] ) ) {
				$post_types = isset( $assoc_args['post-types'] )
					? explode( ',', $assoc_args['post-types'] )
					: [
						'post',
						'page',
						'campaign',
					];
				$query_args = [
					'post_type'           => $post_types,
					'posts_per_page'      => - 1,
					'post_status'         => 'publish',
					'ignore_sticky_posts' => 1,
					'fields'              => 'ids',
				];
				$ids        = get_posts( $query_args );
				$urls       = array_map( 'get_permalink', $ids );
				WP_CLI::log( 'About to purge ' . count( $urls ) . ' urls.' );
			} else {
				WP_CLI::error( 'Please provide either --urls, or purge all urls with --all.' );
			}

			// The following is just a copy of the plugin's dependency chain, can probably be improved.
			$config          = new DefaultConfig( file_get_contents( CLOUDFLARE_PLUGIN_DIR . 'config.json', true ) );
			$logger          = new DefaultLogger( $config->getValue( 'debug' ) );
			$data_store      = new DataStore( $logger );
			$integration_api = new WordPressAPI( $data_store );
			$integration     = new DefaultIntegration( $config, $integration_api, $data_store, $logger );
			$api             = new WordPressClientAPI( $integration );

			$zone_id = $api->getZoneTag( get_option( 'cloudflare_cached_domain_name' ) );

			// 30 is Cloudflare's purge api limit.
			$chunks = array_chunk( $urls, 30 );
			foreach ( $chunks as $i => $chunk ) {
				// We only use $i to be human readable, increment it immediately.
				++ $i;

				$ok = $api->zonePurgeFiles( $zone_id, $chunk );
				// It's unlikely that only some of the chunks will fail, as Cloudflare's API responds with success
				// for any url, even if on non-existent domains. Giving a warning per chunk anyway, just in case.
				if ( ! $ok ) {
					$joined = implode( $chunk, "\n" );
					WP_CLI::warning( "Chunk $i failed, one or more of these didn't work out: \n$joined" );
				}
			}
		};

		WP_CLI::add_command( 'p4-cf-purge', $purge_urls );
	}
}
