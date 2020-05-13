<?php
/**
 * P4 Commands Controller class
 *
 * @package P4GBKS
 * @since 1.40.0
 */

namespace P4GBKS\Command;

use WP_CLI;

/**
 * In this class every method we add becomes a WP_CLI sub-command for `wp p4-blocks`.
 *
 * @package P4BKS\Command
 */
class Controller {

	/**
	 * Sub command that converts shortcodes to Gutenberg html comments
	 *
	 * @param array $args Sub-command parameters.
	 *
	 * @throws WP_CLI\ExitException The thrown exception.
	 */
	public function convert_to_gutenberg( $args ) {

		$start = microtime( true );

		// Supply a post ID as first argument to update a single, specific post.
		$post_id = $args[0] ?? null;

		try {
			WP_CLI::log( 'Converting shortcake shortcodes to gutenberg blocks...' );

			$converter = new Shortcode_To_Gutenberg();
			$converted = $converter->replace_all( $post_id );

			if ( $post_id ) {
				if ( $converted ) {
					WP_CLI::success( "Replaced shortcodes in post $post_id" );
				} else {
					WP_CLI::log( "No shortcodes replaced in post $post_id" );
				}
			} else {
				WP_CLI::success( "Replaced shortcodes in $converted posts" );
			}
		} catch ( \Error $e ) {
			WP_CLI::error( $e->getMessage() );
		} catch ( \Exception $e ) {
			WP_CLI::log( 'Exception: ' . $e->getMessage() );
		}

		$seconds_elapsed = microtime( true ) - $start;
		WP_CLI::log( 'Conversion duration: ' . round( $seconds_elapsed ) . ' seconds' );
	}

	/**
	 * Sub command that removes duplicate postmeta records
	 *
	 * @throws WP_CLI\ExitException The thrown exception.
	 */
	public function remove_duplicate_postmeta() {

		$start = microtime( true );

		try {
			WP_CLI::log( 'Removing duplicate postmeta records...' );

			$deleted_rows = Duplicated_Postmeta::remove();

			if ( $deleted_rows ) {
				WP_CLI::success( "Removed $deleted_rows duplicate postmeta record/s" );
			} else {
				WP_CLI::log( 'No duplicate postmeta record found.' );
			}
		} catch ( \Error $e ) {
			WP_CLI::error( $e->getMessage() );
		} catch ( \Exception $e ) {
			WP_CLI::log( 'Exception: ' . $e->getMessage() );
		}

		$seconds_elapsed = microtime( true ) - $start;
		WP_CLI::log( 'Execution duration: ' . round( $seconds_elapsed ) . ' seconds' );
	}

	// Add here new sub-commands e.g. wp p4-gblocks new_sub_command.
	// public function new_sub_command() {}.
}

