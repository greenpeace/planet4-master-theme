<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use WP_Post;

/**
 * Add the Articles block as a block to all posts that use it with the "Include Articles In Post" settings field, and remove field.
 */
class M011TurnIncludeArticlesSettingIntoBlock extends MigrationScript {

	private const INCLUDE_ARTICLES_META_KEY = 'include_articles';

	/**
	 * Perform the actual migration.
	 *
	 * @param MigrationRecord $record Information on the execution, can be used to add logs.
	 *
	 * @return void
	 */
	protected static function execute( MigrationRecord $record ): void {
		$args = [
			'posts_per_page' => - 1,
			'post_type'      => 'post',
			'meta_query'     => [
				[
					'key'     => self::INCLUDE_ARTICLES_META_KEY,
					'value'   => '',
					'compare' => 'NOT IN',
				],
			],
		];

		$article_posts = get_posts( $args );

		echo 'Converting ' . count( $article_posts ) . " posts with \"Include Articles In Post\" setting to blocks.\n";

		foreach ( $article_posts as $article_post ) {
			self::append_articles_block_from_meta( $article_post );
			delete_post_meta( $article_post->ID, self::INCLUDE_ARTICLES_META_KEY );
		}
	}

	/**
	 * Add the Articles block that is in the meta to the end of the post content.
	 *
	 * @param WP_Post $post A post that has a TAB set through its meta.
	 */
	private static function append_articles_block_from_meta( WP_Post $post ): void {
		if ( 'yes' === $post->include_articles ) {
			// Build the block code for the articles block.
			$articles_block = '<!-- wp:planet4-blocks/articles /-->';

			$args = [
				'ID'           => $post->ID,
				'post_content' => $post->post_content . "\n" .
					$articles_block,
			];

			wp_update_post( $args );
		}
	}
}
