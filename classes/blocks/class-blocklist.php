<?php
/**
 * Blocks tools class
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

use WP_Block_Parser;
use WP_Post;

/**
 * Class BlockList
 *
 * @package P4GBKS\Blocks
 */
class BlockList {
	private const CACHE_GROUP = 'blocklist';
	private const CACHE_KEY   = 'post-%d';

	/**
	 * Check if post contains a specific block.
	 *
	 * @param string   $block_name Full block type to look for.
	 * @param int|null $post_id    Post ID, defaults to current post.
	 *
	 * @return bool Whether the post content contains the specified block.
	 */
	public static function has_block( string $block_name, ?int $post_id = null ): bool {
		return in_array( $block_name, self::get_block_list( $post_id ), true );
	}

	/**
	 * Return name of blocks used in post.
	 *
	 * @param int|null $post_id Post ID, defaults to current post.
	 *
	 * @return string[] List of unique block names.
	 */
	public static function get_block_list( ?int $post_id = null ): array {
		if ( ! $post_id ) {
			$post = get_post( null );
			if ( ! ( $post instanceof WP_Post ) ) {
				return [];
			}

			$post_id = $post->ID;
		}

		$found      = false;
		$block_list = self::cache_get( $post_id, $found );

		if ( ! $found || ! is_array( $block_list ) ) {
			$post       = $post ?? get_post( $post_id );
			$content    = $post->post_content ?? '';
			$block_list = self::parse_block_list( $content );
			self::cache_set( $post_id, $block_list );
		}

		return $block_list;
	}

	/**
	 * List blocks included in post.
	 *
	 * @param string $content Post content.
	 *
	 * @return string[] List of unique block names.
	 */
	public static function parse_block_list( string $content ): array {
		if ( ! has_blocks( $content ) ) {
			return [];
		}

		$blocks = ( new WP_Block_Parser() )->parse( $content );
		$parsed = array_filter( $blocks, fn ( $b ) => ! empty( $b['blockName'] ) );

		$list = [];
		while ( ! empty( $parsed ) ) {
			$block = array_shift( $parsed );
			if ( ! empty( $block['innerBlocks'] ) ) {
				$parsed = array_merge( $parsed, $block['innerBlocks'] );
			}

			// Add current block to list.
			if ( ! empty( $block['blockName'] ) ) {
				$list[] = $block['blockName'];
			}

			// Add reusable-block blocks to list.
			if ( 'core/block' === $block['blockName'] && isset( $block['attrs']['ref'] ) ) {
				$ref_id = (int) $block['attrs']['ref'];
				if ( $ref_id ) {
					$list = array_merge( $list, self::get_block_list( $ref_id ) );
				}
			}
		}

		return array_unique( $list );
	}

	/**
	 * @param int  $post_id Post ID.
	 * @param bool $found   Cache found.
	 * @return mixed
	 */
	private static function cache_get( int $post_id, &$found = false ) {
		return wp_cache_get(
			sprintf( self::CACHE_KEY, $post_id ),
			self::CACHE_GROUP,
			false,
			$found
		);
	}

	/**
	 * @param int   $post_id Post ID.
	 * @param array $data    Data.
	 * @return bool
	 */
	private static function cache_set( int $post_id, array $data ): bool {
		return wp_cache_set(
			sprintf( self::CACHE_KEY, $post_id ),
			$data,
			self::CACHE_GROUP
		);
	}

	/**
	 * @param int $post_id Post ID.
	 * @return bool
	 */
	public static function cache_delete( $post_id ): bool {
		return wp_cache_delete(
			sprintf( self::CACHE_KEY, (int) $post_id ),
			self::CACHE_GROUP
		);
	}
}
