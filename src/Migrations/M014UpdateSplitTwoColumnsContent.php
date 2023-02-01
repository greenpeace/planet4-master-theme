<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4GBKS\Search\BlockSearch;
use WP_Block_Parser;

use P4GBKS\Blocks\SplitTwoColumns;

/**
 * Update Happy Point attributes to latest version
 */
class M014UpdateSplitTwoColumnsContent extends MigrationScript {
	/**
	 * Perform the actual migration.
	 *
	 * @param MigrationRecord $record Information on the execution, can be used to add logs.
	 *
	 * @return void
	 */
	public static function execute( MigrationRecord $record ): void {
		$search     = new BlockSearch();
		$parser     = new WP_Block_Parser();
		$block_name = 'planet4-blocks/split-two-columns';

		$params = ( new \P4GBKS\Search\Block\Query\Parameters() )
			->with_name( $block_name )
			->with_post_status( [ 'publish', 'private', 'draft' ] )
			->with_post_type( [ 'post', 'page', 'campaign' ] );
		$post_ids = $search->get_posts( $params );
		var_dump( $post_ids );
		if ( empty( $post_ids ) ) {
			return;
		}

		$args  = [
			'include'     => $post_ids,
			'post_type'   => [ 'post', 'page', 'campaign' ],
			'post_status' => [ 'publish', 'private', 'draft' ],
		];
		$posts = get_posts( $args ) ?? [];

		foreach ( $posts as $post ) {
			if ( empty( $post->post_content ) ) {
				continue;
			}

			$blocks          = $parser->parse( $post->post_content );
			$updated_content = $post->post_content;

			foreach ( $blocks as $block ) {
				// Skip other blocks.
				if ( ! isset( $block['blockName'] ) || $block['blockName'] !== $block_name ) {
					continue;
				}

				// Skip updated blocks.
				if ( isset( $block['attrs']['version'] ) && $block['attrs']['version'] >= 4 ) {
					continue;
				}

				$current_html = self::compile_block_html( $block );
				if ( empty( $current_html )
					|| false === strpos( $post->post_content, $current_html )
				) {
					var_dump( $current_html, $post->post_content );
					// Compilation function error or invalid output, skipping.
					continue;
				}

				$updated_block = self::update_block( $block );
				$updated_html  = self::compile_block_html( $updated_block );

				if ( $updated_html !== $current_html ) {
					echo "\n", $post->ID, ', ', $post->post_title, "\n";
					echo "\nReplacing:\n";
					echo $current_html;
					echo "\nWith:\n";
					echo $updated_html;
					echo "\n";
				}

				// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				//echo "\n", $post->ID, ', ', $post->post_title, "\n";
				//echo $current_html, "\n";
				//echo $updated_html, "\n";

				$updated_content = str_replace(
					$current_html,
					$updated_html,
					$updated_content
				);
			}

			// Blocks were updated, saving.
			if ( $updated_content !== $post->post_content ) {
				echo "Updating...\n";
				wp_update_post(
					[
						'ID'           => $post->ID,
						'post_content' => $updated_content,
					]
				);
				echo "Done.\n\n";
			}
		}
	}

	/**
	 * Update block data to last version.
	 *
	 * @param array $block Block.
	 *
	 * @return array Updated block.
	 */
	public static function update_block( array $block ): array {
		$block['attrs'] = SplitTwoColumns::update_data( $block['attrs'] );

		$block['innerHTML'] = "\n<section class=\"alignfull split-two-column "
			. ( $block['attrs']['className'] ?? '' ) . '">'
			. '<div class="split-two-column-item item--left">'
			. self::issue_image( $block['attrs'] )
			. '<div class="split-two-column-item-content">'
			. self::issue_title( $block['attrs'] )
			. self::issue_description( $block['attrs'] )
			. self::issue_link( $block['attrs'] )
			. '</div></div>'
			. '<div class="split-two-column-item item--right">'
			. self::tag_image( $block['attrs'] )
			. '<div class="split-two-column-item-content">'
			. self::tag_name( $block['attrs'] )
			. self::tag_description( $block['attrs'] )
			. self::button( $block['attrs'] )
			. '</div></div>'
			. "</section>\n";

		return $block;
	}

	// Template functions.

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function issue_image( array $attrs ): string {
		if ( empty( $attrs['issue_image_src'] ) ) {
			return '';
		}

		return '<div class="split-two-column-item-image">'
			. '<img src="' . $attrs['issue_image_src'] . '" '
				. 'srcset="' . $attrs['issue_image_srcset'] . '" '
				. 'alt="' . $attrs['issue_image_title'] . '" '
				. 'title="' . $attrs['issue_image_title'] . '" '
				. 'style="object-position:' . ( $attrs['focus_issue_image'] ?? '50% 50%' ) . '" '
				. 'sizes="' . "\n" . '(min-width: 580px) 80vw,' . "\n" . ' 100vw"/></div>';
	}

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function issue_title( array $attrs ): string {
		if ( empty( $attrs['title'] ) ) {
			return '';
		}

		if ( empty( $attrs['issue_link_path'] ) ) {
			return '<h2 class="split-two-column-item-title">' . $attrs['title'] . '</h2>';
		}

		return '<h2 class="split-two-column-item-title">'
			. '<a href="' . $attrs['issue_link_path'] . '" '
				. 'data-ga-category="Split Two Columns" '
				. 'data-ga-action="Category Title" '
				. 'data-ga-label="n/a">' . $attrs['title'] . '</a></h2>';
	}

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function issue_description( array $attrs ): string {
		if ( empty( $attrs['issue_description'] ) ) {
			return '';
		}

		return '<p class="split-two-column-item-subtitle">'
			. $attrs['issue_description'] . '</p>';
	}

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function issue_link( array $attrs ): string {
		if ( empty( $attrs['issue_link_text'] ) || empty( $attrs['issue_link_path'] ) ) {
			return '';
		}

		return '<a class="split-two-column-item-link" '
			. 'href="' . $attrs['issue_link_path'] . '" '
			. 'data-ga-category="Split Two Columns" '
			. 'data-ga-action="Text Link" '
			. 'data-ga-label="n/a">' . $attrs['issue_link_text'] . '</a>';
	}

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function tag_image( array $attrs ): string {
		if ( empty( $attrs['tag_image_src'] ) ) {
			return '';
		}

		return '<div class="split-two-column-item-image">'
			. '<img src="' . $attrs['tag_image_src'] . '" '
				. 'srcset="' . $attrs['tag_image_srcset'] . '" '
				. 'alt="' . $attrs['tag_image_title'] . '" '
				. 'title="' . $attrs['tag_image_title'] . '" '
				. 'style="object-position:' . ( $attrs['focus_tag_image'] ?? '50% 50%' ) . '" '
				. 'sizes="' . "\n" . '(min-width: 580px) 75vw,' . "\n" . ' 1vw"/></div>';
	}

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function tag_name( array $attrs ): string {
		if ( empty( $attrs['tag_name'] ) ) {
			return '';
		}

		return '<a class="split-two-column-item-tag" '
			. 'href="' . $attrs['tag_link'] . '" '
			. 'data-ga-category="Split Two Columns" '
			. 'data-ga-action="Tag Title" '
			. 'data-ga-label="n/a">'
			. '<span aria-label="hashtag">#</span>' . $attrs['tag_name'] . '</a>';
	}

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function tag_description( array $attrs ): string {
		if ( empty( $attrs['tag_description'] ) ) {
			return '';
		}

		return '<p class="split-two-column-item-subtitle">'
			. $attrs['tag_description'] . '</p>';
	}

	/**
	 * @param array $attrs Block attributes.
	 */
	private static function button( array $attrs ): string {
		if ( empty( $attrs['button_text'] ) || empty( $attrs['button_link'] ) ) {
			return '';
		}

		return '<a class="btn btn-primary btn-block split-two-column-item-button" '
			. 'href="' . $attrs['button_link'] . '" '
			. 'data-ga-category="Split Two Columns" '
			. 'data-ga-action="Call to Action" '
			. 'data-ga-label="n/a">' . $attrs['button_text'] . '</a>';
	}

	/**
	 * Compile block to HTML version.
	 *
	 * @param array $block Block.
	 *
	 * @return string Block HTML.
	 */
	public static function compile_block_html( array $block ): string {
		if ( empty( $block['innerHTML'] ) ) {
			return sprintf(
				'<!-- wp:%s %s /-->',
				$block['blockName'],
				serialize_block_attributes( $block['attrs'] )
			);
		}

		return sprintf(
			"<!-- wp:%s %s -->%s<!-- /wp:%s -->",
			$block['blockName'],
			serialize_block_attributes( $block['attrs'] ),
			$block['innerHTML'],
			$block['blockName']
		);
	}
}
