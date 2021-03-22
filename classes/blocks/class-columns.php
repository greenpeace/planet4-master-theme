<?php
/**
 * Columns block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Columns_Controller
 *
 * @package P4BKS
 * @since 0.1
 */
class Columns extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'columns';

	const TASK_TEMPLATE_NAME = 'tasks';

	const LAYOUT_NO_IMAGE = 'no_image';
	const LAYOUT_TASKS    = 'tasks';
	const LAYOUT_ICONS    = 'icons';
	const LAYOUT_IMAGES   = 'image';
	const MAX_COLUMNS     = 4;

	/**
	 * Columns constructor.
	 */
	public function __construct() {
		register_block_type(
			self::get_full_block_name(),
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'columns_block_style' => [
						'type' => 'string',
					],
					'columns_title'       => [
						'type' => 'string',
					],
					'columns_description' => [
						'type' => 'string',
					],
					'columns'             => [
						'type'    => 'array',
						'default' => [],
						'items'   => [
							'type'       => 'object',
							// In JSON Schema you can specify object properties in the properties attribute.
							'properties' => [
								'title'        => [
									'type' => 'string',
								],
								'description'  => [
									'type' => 'string',
								],
								'attachment'   => [
									'type' => 'integer',
								],
								'cta_link'     => [
									'type' => 'string',
								],
								'link_new_tab' => [
									'type' => 'boolean',
								],
								'cta_text'     => [
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
}
