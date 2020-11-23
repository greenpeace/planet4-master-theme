<?php
/**
 * Accordion block class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Accordion
 *
 * @package P4GBKS\Blocks
 */
class Accordion extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'accordion';

	/**
	 * Accordion constructor.
	 */
	public function __construct() {
		register_block_type(
			'planet4-blocks-beta/accordion',
			[
				'editor_script' => 'planet4-blocks-beta',
				'attributes'    => [
					'title'       => [
						'type'    => 'string',
						'default' => '',
					],
					'description' => [
						'type'    => 'string',
						'default' => '',
					],
					'tabs'        => [
						'type'    => 'array',
						'default' => [],
						'items'   => [
							'type'       => 'object',
							// In JSON Schema you can specify object properties in the properties attribute.
							'properties' => [
								'headline'       => [
									'type'    => 'string',
									'default' => '',
								],
								'text'           => [
									'type'    => 'string',
									'default' => '',
								],
								'button_text'    => [
									'type'    => 'string',
									'default' => '',
								],
								'button_url'     => [
									'type'    => 'string',
									'default' => '',
								],
								'button_new_tab' => [
									'type'    => 'boolean',
									'default' => false,
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
