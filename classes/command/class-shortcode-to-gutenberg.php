<?php
/**
 * Shortcode to Gutenberg conversion command
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command;

use P4GBKS\Command\Converters\Shortcode_Converter_Factory;
use WP_Query;
use WP_CLI;

/**
 * Class for updating old shortcodes to Gutenberg blocks
 */
class Shortcode_To_Gutenberg {
	/**
	 * @var bool
	 */
	protected $initialised = false;

	/**
	 * Removes all existing shortcodes and replaces them with only the ones we're interested in
	 */
	public function init() {
		if ( ! $this->initialised ) {
			remove_all_shortcodes();

			// Blocks to convert.
			$blocks = [
				'shortcake_articles',
				'shortcake_columns',
				'shortcake_carousel_header',
				'shortcake_cookies',
				'shortcake_counter',
				'shortcake_enblock',
				'shortcake_gallery',
				'shortcake_happy_point',
				'shortcake_media_video',
				'shortcake_newcovers',
				'shortcake_social_media',
				'shortcake_split_two_columns',
				'shortcake_submenu',
				'shortcake_timeline',
				'shortcake_take_action_boxout',
			];

			foreach ( $blocks as $block ) {
				add_shortcode( $block, [ $this, 'convert_to_gutenberg' ] );
			}
			$this->initialised = true;
		}
	}

	/**
	 * Runs $this->update_text on every post's post_content, and updates the database whenever it changes
	 *
	 * @param int $post_id - if you wish to convert one post only (for testing purposes).
	 *
	 * @return int
	 * @throws \Exception - if no posts found.
	 */
	public function replace_all( $post_id ) {
		$args = [
			'post_type'        => 'any',
			'post_status'      => [ 'publish', 'pending', 'draft', 'future', 'private' ],
			'nopaging'         => true,
			'suppress_filters' => true,
		];

		if ( $post_id ) {
			$args['p'] = $post_id;
		}

		$query = new WP_Query( $args );
		$posts = $query->get_posts();

		if ( ! $posts ) {
			throw new \Exception( 'No posts found' );
		}

		$updated = 0;
		foreach ( $posts as $post ) {
			$post->post_content = $this->update_text( $post->post_content );
			wp_update_post( $post );
			$updated ++;

			WP_CLI::log( "Updated post $post->ID" );
		}

		return $updated;
	}

	/**
	 * Converts any specified shortcodes in the given text
	 *
	 * @param string $text - post_content no doubt.
	 *
	 * @return string
	 */
	protected function update_text( $text ) {
		$this->init();

		return do_shortcode( $text );
	}

	/**
	 * Convert shortcodes to Gutenberg comments:
	 *   shortcake_carousel -> shortcake_gallery
	 *
	 * Examples:
	 * Old:
	 * [shortcake_articles article_heading="Le Articles title" read_more_text="Le Button Text" articles_description="Le Articles description" read_more_link="https://greenpeace.org" post_types="15,16,14" button_link_new_tab="false" tags="6,8" article_count="5" ignore_categories="false" /]
	 * New:
	 * <!-- wp:planet4-blocks/articles {"title":"Le Articles title","description":"Le Articles description","tags":[5,6],"count":"5","read_more_text":"Le Button Text","read_more_link":"https://greenpeace.org","button_link_new_tab":"false"} /-->
	 *
	 * @param string[] $attrs - shortcode attributes.
	 * @param string[] $content - shortcode content.
	 * @param string[] $shortcode_tag - shortcode tag.
	 *
	 * @return string
	 */
	public function convert_to_gutenberg( $attrs, $content, $shortcode_tag ) {

		$converter = Shortcode_Converter_Factory::get_converter( $shortcode_tag, $attrs );
		return $converter->convert();
	}
}
