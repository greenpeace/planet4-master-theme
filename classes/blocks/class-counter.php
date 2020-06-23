<?php
/**
 * Counter block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Counter
 *
 * @package P4GBKS\Blocks
 */
class Counter extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'counter';

	/**
	 * Counter constructor.
	 */
	public function __construct() {
		register_block_type(
			'planet4-blocks/counter',
			[  // - Register the block for the editor
				'editor_script'   => 'planet4-blocks',  // in the PHP side.
				'attributes'      => [
					'title'         => [
						'type'    => 'string',
						'default' => '',
					],
					'description'   => [
						'type'    => 'string',
						'default' => '',
					],
					'style'         => [
						'type'    => 'string',
						'default' => 'plain',
					],
					'completed'     => [
						'type'    => 'integer',
						'default' => 0,
					],
					'completed_api' => [
						'type' => 'string',
					],
					'target'        => [
						'type'    => 'integer',
						'default' => 0,
					],
					'text'          => [
						'type'    => 'text',
						'default' => '',
					],
				],
			]
		);

		register_block_style(
			'planet4-blocks/counter',
			array(
				'name'			=> 'plain',
				'label'			=> __('Text Only', 'p4ge')
			)
		);

		register_block_style(
			'planet4-blocks/counter',
			array(
				'name'			=> 'bar',
				'label'			=> __('Progress Bar', 'p4ge')
			)
		);

		register_block_style(
			'planet4-blocks/counter',
			array(
				'name'			=> 'arc',
				'label'			=> __('Progress Dial', 'p4ge')
			)
		);

		register_block_style(
			'planet4-blocks/counter',
			array(
				'name'			=> 'en-forms-bar',
				'label'			=> __('Progress bar inside EN Form', 'p4ge')
			)
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
