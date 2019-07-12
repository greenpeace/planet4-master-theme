<?php
/**
 * Submenu block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;


/**
 * Class SubMenu_Controller
 *
 * @package P4BKS\Controllers\Blocks
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
					'submenu_style'  => [
						'type'    => 'integer',
						'default' => 1,
					],
					'title'       => [
						'type'    => 'string',
						'default' => '',
					],
					'heading1'        => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					'link1'  => [
						'type'  => 'boolean',
					],
					'style1'  => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					'heading2'        => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
						],
					],
					'link2'  => [
						'type'  => 'boolean',
					],
					'style2'  => [
						'type'  => 'array',
						'items' => [
							'type' => 'string',
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
	 * @param string $content This is the post content.
	 * @param string $shortcode_tag The shortcode tag of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes, $content = '', $shortcode_tag = 'shortcake_' . self::BLOCK_NAME ): array {

		global $post;

		$content = $post->post_content;
//		$menu    = $this->parse_post_content( $content, $attributes );

//		wp_enqueue_script( 'submenu', P4GBKS_ADMIN_DIR . 'js/submenu.js', [ 'jquery' ], '0.2', true );
//		wp_localize_script( 'submenu', 'submenu', $menu );

		$block_data = [
			'title' => $attributes['title'] ?? '',
//			'menu'  => $menu,
			'style' => $attributes['submenu_style'] ?? '1',
		];

		return $block_data;
	}

	/**
	 * Parse post's content to extract headings and build menu
	 *
	 * @param string $content Post content.
	 * @param array $attributes Submenu block attributes.
	 *
	 * @return array
	 */
	private function parse_post_content( $content, $attributes ) {

		// Validate, if $content is empty.
		if ( ! $content ) {
			return [];
		}

		// make array of heading level metadata keyed by tag name.
		$heading_meta = [];
		foreach ( range( 1, 3 ) as $heading_num ) {
			$heading = $this->heading_attributes( $heading_num, $attributes );
			if ( ! $heading ) {
				break;
			}
			$heading['level']                = $heading_num;
			$heading_meta[ $heading['tag'] ] = $heading;
		}

		$dom = new \DOMDocument();
		$dom->loadHtml( $content );
		$xpath = new \DOMXPath( $dom );

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
	 * @param int $menu_level Level 1, 2 or 3.
	 * @param array $attributes Shortcode UI attributes.
	 *
	 * @return array|null associative array or null if menu level is not configured
	 */
	private function heading_attributes( $menu_level, $attributes ) {
		return empty( $attributes[ 'heading' . $menu_level ] )
			? null
			: [
				'heading' => $attributes[ 'heading' . $menu_level ],
				'tag'     => 'h' . $attributes[ 'heading' . $menu_level ],
				'link'    => $attributes[ 'link' . $menu_level ] ?? false,
				'style'   => $attributes[ 'style' . $menu_level ] ?? 'none',
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

