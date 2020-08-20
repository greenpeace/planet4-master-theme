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
					'accordion_title'       => [
						'type'    => 'string',
						'default' => '',
					],
					'accordion_description' => [
						'type'    => 'string',
						'default' => '',
					],
					'accordion_rows'        => [
						'type'          => 'array',
						'default'       => [],
						'accordion_row' => [
							'type'       => 'object',
							// In JSON Schema you can specify object properties in the properties attribute.
							'properties' => [
								'accordion_id'        => [
									'type'    => 'integer',
									'default' => '',
								],
								'accordion_headline'  => [
									'type'    => 'string',
									'default' => '',
								],
								'accordion_text'      => [
									'type'    => 'string',
									'default' => '',
								],
								'accordion_btn_text'  => [
									'type'    => 'string',
									'default' => '',
								],
								'accordion_btn_url'   => [
									'type'    => 'string',
									'default' => '',
								],
								'button_link_new_tab' => [
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
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {
		$fields = [
			'accordion_title'       => $attributes['accordion_title'] ?? '',
			'accordion_description' => $attributes['accordion_description'] ?? '',
			'accordion_rows'        => $attributes['accordion_rows'] ?? '',
			'accordion_id'          => $attributes['accordion_id'] ?? '',
			'accordion_headline'    => $attributes['accordion_headline'] ?? '',
			'accordion_text'        => $attributes['accordion_text'] ?? '',
			'accordion_btn_text'    => $attributes['accordion_btn_text'] ?? '',
			'accordion_btn_url'     => $attributes['accordion_btn_url'] ?? '',
			'button_link_new_tab'   => $attributes['button_link_new_tab'] ?? '',

		];

		$block_data = [
			'fields'              => $fields,
			'available_languages' => P4GBKS_LANGUAGES,
		];

		return $block_data;
	}

}
