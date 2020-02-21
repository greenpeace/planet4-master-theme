<?php
/**
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * @package P4GBKS\Blocks
 * @since 0.1
 */
class SubPages extends Base_Block {

	/** @const string BLOCK_NAME */
	const BLOCK_NAME = 'sub-pages';

	/** @const string EMPTY_MESSAGE */
	const EMPTY_MESSAGE = 'The current page has no sub pages.';

	/**
	 * Submenu constructor.
	 */
	public function __construct() {
		register_block_type(
			'planet4-blocks/sub-pages',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {
		global $post;

		// If request is coming from backend rendering.
		if ( $this->is_rest_request() ) {
			$post_id = filter_input( INPUT_GET, 'post_id', FILTER_VALIDATE_INT );
		} else {
			$post_id = $post->ID;
		}

		$children = get_children(
			[
				'post_parent' => $post_id,
				'post_type'   => 'campaign',
			]
		);

		$sub_pages = array_map(
			static function ( $page ) {
				return [
					'link'  => get_permalink( $page->ID ),
					'title' => $page->post_title,
				];
			},
			$children
		);

		return [
			'sub_pages' => $sub_pages,
		];
	}

}

