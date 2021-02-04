<?php

namespace P4\MasterTheme\Commands;

use CF\Integration\DefaultConfig;
use CF\Integration\DefaultIntegration;
use CF\Integration\DefaultLogger;
use CF\WordPress\DataStore;
use CF\WordPress\WordPressAPI;
use CF\WordPress\WordPressClientAPI;
use P4\MasterTheme\Features;
use WP_CLI;

/**
 * Class CloudflarePurge
 */
class CloudflarePurge extends Command {

	/**
	 * The name to access the command.
	 *
	 * @return string The command name.
	 */
	protected static function get_name(): string {
		return 'p4-cf-purge';
	}

	/**
	 * The logic of the command. Has WP_CLI command signature.
	 *
	 * @param array|null $args Positional arguments.
	 * @param array|null $assoc_args Named arguments.
	 */
	public static function execute( ?array $args, ?array $assoc_args ): void {
		if ( ! Features::is_active( Features::CLOUDFLARE_DEPLOY_PURGE ) ) {
			WP_CLI::warning( 'Purge on deploy is not enabled, not purging.' );

			return;
		}

		if ( ! defined( 'CLOUDFLARE_PLUGIN_DIR' ) ) {
			define( 'CLOUDFLARE_PLUGIN_DIR', WP_PLUGIN_DIR . '/cloudflare/' );
		}
		require_once CLOUDFLARE_PLUGIN_DIR . 'vendor/autoload.php';

		// The following is just a copy of the plugin's dependency chain, can probably be improved.
		$config          = new DefaultConfig( file_get_contents( CLOUDFLARE_PLUGIN_DIR . 'config.json', true ) );
		$logger          = new DefaultLogger( $config->getValue( 'debug' ) );
		$data_store      = new DataStore( $logger );
		$integration_api = new WordPressAPI( $data_store );
		$integration     = new DefaultIntegration( $config, $integration_api, $data_store, $logger );
		$api             = new WordPressClientAPI( $integration );

		$zone_id = $api->getZoneTag( get_option( 'cloudflare_cached_domain_name' ) );

		$urls = self::get_urls( $assoc_args );
		WP_CLI::log( 'About to purge ' . count( $urls ) . ' urls.' );

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
	}

	/**
	 * Determine which urls to purge. Throws error if right args were not passed.
	 *
	 * @param array|null $assoc_args The named args passed to the command.
	 *
	 * @throws \RuntimeException If you don't provide the right args.
	 *
	 * @return array The urls to purge
	 */
	private static function get_urls( ?array $assoc_args ): array {
		if ( isset( $assoc_args['urls'] ) ) {
			return explode( ',', $assoc_args['urls'] );
		}

		if ( isset( $assoc_args['all'] ) ) {
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

			$ids = get_posts( $query_args );

			return array_map( 'get_permalink', $ids );
		}

		throw new \RuntimeException( 'Please provide either --urls, or purge all urls with --all.' );
	}
}
