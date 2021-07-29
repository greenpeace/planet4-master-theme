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
			self::get_full_block_name(),
			[
				'editor_script'   => 'planet4-blocks',
				// todo: Remove when all content is migrated.
				'render_callback' => [ self::class, 'render_frontend' ],
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
						'type'    => 'array',
						'items'   => [
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
						'default' => [
							[
								'heading' => 2,
								'link'    => false,
								'style'   => 'none',
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
}

