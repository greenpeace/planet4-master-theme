<?php
/**
 * Submenu block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

use DOMDocument;
use DOMXPath;

/**
 * Class SubMenu
 *
 * @package P4GBKS\Blocks
 * @since 0.1
 */
class Submenu extends Base_Block {

	/** @const string BLOCK_NAME */
	const BLOCK_NAME = 'submenu';

	public function __construct() {
		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			'planet4-blocks/submenu',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'submenu_style' => [
						'type'    => 'integer',
						'default' => 1,
					],
					'title'         => [
						'type'    => 'string',
						'default' => '',
					],
					/**
					 * levels is an array of objects.
					 * Object structure:
					 * {
					 *   heading: 'integer'
					 *   link: 'boolean'
					 *   style: 'string'
					 * }
					 */
					'levels'        => [
						'type'  => 'array',
						'items' => [
							'type' => 'object',
						],
					],
				],
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

		// If request is coming from backend rendering.
		if ( $this->is_rest_request() ) {
			$post_id = filter_input( INPUT_GET, 'post_id', FILTER_VALIDATE_INT );
			if ( $post_id > 0 ) {
				$post = get_post( $post_id );
			}
		} else {
			$post = get_queried_object();
		}

		$menu = [];
		if ( ! is_null( $post ) && isset( $attributes['levels'] ) ) {
			$content = $post->post_content;
			$menu    = $this->parse_post_content( $content, $attributes['levels'] );
		}

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			wp_enqueue_script( 'submenu', P4GBKS_PLUGIN_URL . 'public/js/submenu.js', [ 'jquery' ], '0.2', true );
			wp_localize_script( 'submenu', 'submenu', $menu );
		}

		$block_data = [
			'title' => $attributes['title'] ?? '',
			'menu'  => $menu,
			'style' => $attributes['submenu_style'] ?? '1',
		];

		return $block_data;
	}

	/**
	 * Parse post's content to extract headings and build menu
	 *
	 * @param string $content Post content.
	 * @param array  $levels Submenu block attributes.
	 *
	 * @return array
	 */
	private function parse_post_content( $content, $levels ) {

		// Validate, if $content is empty.
		if ( ! $content || is_null( $levels ) ) {
			return [];
		}

		// make array of heading level metadata keyed by tag name.
		$heading_meta = [];
		$index=1;
		foreach ( $levels as $level ) {
			$heading = $this->heading_attributes( $level );
			if ( ! $heading ) {
				break;
			}
			$heading['level']                = $index++;
			$heading_meta[ $heading['tag'] ] = $heading;
		}

		$dom = new DOMDocument();
		libxml_use_internal_errors( true );

		$dom->loadHtml( $content );
		$xpath = new DOMXPath( $dom );

		// get all the headings as an array of nodes.
		$xpath_expression = '//' . join( ' | //', array_keys( $heading_meta ) );
		$node_list        = $xpath->query( $xpath_expression );
		$nodes            = iterator_to_array( $node_list );

		// process nodes array recursively to build menu.
		return $this->build_menu( 1, $nodes, $heading_meta );
	}

	/**
	 * Extract shortcode attributes for given heading level.
	 *
	 * @param array $level Block level attributes.
	 *
	 * @return array|null associative array or null if menu level is not configured
	 */
	private function heading_attributes( $level ) {
		return empty( $level )
			? null
			: [
				'heading' => $level['heading'],
				'tag'     => 'h' . $level['heading'],
				'link'    => $level['link'] ?? false,
				'style'   => $level['style'] ?? 'none',
			];
	}

	/**
	 * Process flat array of DOM nodes to build up menu tree structure.
	 *
	 * @param int $current_level Current menu nesting level.
	 * @param \DOMNode[] $nodes Array of heading DOM nodes, passed by reference.
	 * @param array $heading_meta Metadata about each heading tag.
	 *
	 * @return array menu tree structure
	 */
	private function build_menu( $current_level, &$nodes, $heading_meta ) {
		$menu = [];

		// phpcs:ignore Squiz.PHP.DisallowSizeFunctionsInLoops.Found
		while ( count( $nodes ) ) {
			// consider first node in the list but don't remove it yet.
			$node = $nodes[0];

			$heading = $heading_meta[ $node->nodeName ];
			if ( $heading['level'] > $current_level ) {
				if ( count( $menu ) === 0 ) {
					// we're skipping over a heading level so create an empty node.
					$menu[] = new \stdClass();
				}
				$menu[ count( $menu ) - 1 ]->children = $this->build_menu( $current_level + 1, $nodes, $heading_meta );
			} elseif ( $heading['level'] < $current_level ) {
				return $menu;
			} else {
				$menu[] = $this->create_menu_item( $node->nodeValue, $heading['tag'], $heading['link'], $heading['style'] );

				// remove node from list only once it has been added to the menu.
				array_shift( $nodes );
			}
		}

		return $menu;
	}

	/**
	 * Create a std object representing a node/heading.
	 *
	 * @param string $text Heading/menu item text.
	 * @param string $type Type/name of the tag.
	 * @param bool|string $link True if this menu item should link to the heading.
	 * @param string $style List style for menu item.
	 *
	 * @return \stdClass
	 */
	private function create_menu_item( $text, $type, $link, $style ) {
		$menu_obj           = new \stdClass();
		$menu_obj->text     = utf8_decode( $text );
		$menu_obj->hash     = md5( $text );
		$menu_obj->type     = $type;
		$menu_obj->style    = $style;
		$menu_obj->link     = filter_var( $link, FILTER_VALIDATE_BOOLEAN );
		$menu_obj->id       = sanitize_title( utf8_decode( $text ) );
		$menu_obj->children = [];

		return $menu_obj;
	}
}

