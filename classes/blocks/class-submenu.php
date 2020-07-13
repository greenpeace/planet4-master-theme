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

	/**
	 * Submenu constructor.
	 */
	public function __construct() {
		register_block_type(
			'planet4-blocks/submenu',
			[
				'editor_script'   => 'planet4-blocks',
				// todo: Remove when all content is migrated.
				'render_callback' => static function ( $attributes ) {
					$json = wp_json_encode( [ 'attributes' => $attributes ] );
					return '<div data-render="planet4-blocks/submenu" data-attributes="' . htmlspecialchars( $json ) . '"></div>';
				},
				'attributes'      => [
					'title'         => [
						'type'    => 'string',
						'default' => '',
					],
					'submenu_style' => [ // Needed for old blocks conversion.
						'type'    => 'integer',
						'default' => 0,
					],
					/**
					 * Levels is an array of objects.
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
							'type'       => 'object',
							// In JSON Schema you can specify object properties in the properties attribute.
							'properties' => [
								'heading' => [
									'type' => 'integer',
								],
								'link'    => [
									'type' => 'boolean',
								],
								'style'   => [
									'type' => 'string',
								],
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Required by the `Base_Block` class.
	 *
	 * @param array $fields Unused, required by the abstract function.
	 */
	public function prepare_data( $fields ): array {
		return [];
	}

	/**
	 * Get the menu items from the post data.
	 *
	 * @param array $fields The fields entered in the editor.
	 *
	 * @return array The menu items to be passed in the View.
	 */
	public static function get_menu_items( $fields ): array {

		if ( isset( $fields['post_id'] ) ) {
			$post = get_post( $fields['post_id'] );
		}

		$menu = [];
		if ( ! is_null( $post ) && isset( $fields['levels'] ) ) {
			$content = $post->post_content;
			$menu    = self::parse_post_content( $content, $fields['levels'] );
		}

		return $menu;
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
		$index        = 1;
		foreach ( $levels as $level ) {
			$heading = self::heading_attributes( $level );
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
		return self::build_menu( 1, $nodes, $heading_meta );
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
	 * @param int        $current_level Current menu nesting level.
	 * @param \DOMNode[] $nodes Array of heading DOM nodes, passed by reference.
	 * @param array      $heading_meta Metadata about each heading tag.
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
				$menu[ count( $menu ) - 1 ]->children = self::build_menu( $current_level + 1, $nodes, $heading_meta );
			} elseif ( $heading['level'] < $current_level ) {
				return $menu;
			} else {
				$menu[] = self::create_menu_item( $node->nodeValue, $heading['tag'], $heading['link'], $heading['style'] );

				// remove node from list only once it has been added to the menu.
				array_shift( $nodes );
			}
		}

		return $menu;
	}

	/**
	 * Create a std object representing a node/heading.
	 *
	 * @param string      $text Heading/menu item text.
	 * @param string      $type Type/name of the tag.
	 * @param bool|string $link True if this menu item should link to the heading.
	 * @param string      $style List style for menu item.
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

