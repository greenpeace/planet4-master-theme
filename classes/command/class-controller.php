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
			WP_CLI::log( "Removing duplicate postmeta records...\n" );

			$this->print_duplicate_postmeta();

			$deleted_rows = Duplicated_Postmeta::remove();

			if ( $deleted_rows ) {
				WP_CLI::success( "Removed $deleted_rows duplicate postmeta records" );
			} else {
				WP_CLI::log( 'No whitelisted duplicate postmeta records found.' );
			}
		} catch ( \Error $e ) {
			WP_CLI::error( $e->getMessage() );
		} catch ( \Exception $e ) {
			WP_CLI::log( 'Exception: ' . $e->getMessage() );
		}

		$seconds_elapsed = microtime( true ) - $start;
		WP_CLI::log( 'Execution duration: ' . round( $seconds_elapsed ) . ' seconds' );
	}

	/**
	 * Print duplicate meta_key's with counts.
	 */
	public function print_duplicate_postmeta() {
		$duplicate_metakey_counts = Duplicated_Postmeta::detect();

		if ( $duplicate_metakey_counts ) {
			WP_CLI::log( 'No.	Count	Name' );
			$whitelisted_meta_keys = Duplicated_Postmeta::META_KEY_LIST;
			foreach ( $duplicate_metakey_counts as $id => $meta_key ) {
				$delete_marker = in_array( $meta_key->meta_key, $whitelisted_meta_keys, true ) ? '*' : '';
				WP_CLI::log( ( $id + 1 ) . '	' . ( $meta_key->all_count - $meta_key->unique_count ) . '	' . $meta_key->meta_key . ' ' . $delete_marker );
			}
			WP_CLI::log( "\nThe \"*\" indicates whitelisted metakey's for delete operation\n" );
		}
	}

	// Add here new sub-commands e.g. wp p4-gblocks new_sub_command.
	// public function new_sub_command() {}.
}

