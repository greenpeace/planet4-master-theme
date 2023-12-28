<?php
/**
 * Counter block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

use WP_Block_Type_Registry;

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
		if ( WP_Block_Type_Registry::get_instance()->is_registered( self::get_full_block_name() ) ) {
			return;
		}

		register_block_type(
			self::get_full_block_name(),
			[  // - Register the block for the editor
				'editor_script' => 'planet4-blocks',  // in the PHP side.
				'attributes'    => [
					'title'         => [
						'type'    => 'string',
						'default' => '',
					],
					'description'   => [
						'type'    => 'string',
						'default' => '',
					],
					'completed'     => [
						'type'    => 'integer',
						'default' => '',
					],
					'completed_api' => [
						'type' => 'string',
					],
					'target'        => [
						'type'    => 'integer',
						'default' => '',
					],
					'text'          => [
						'type'    => 'text',
						'default' => '',
					],
					'style'         => [ // Needed to convert existing blocks.
						'type'    => 'string',
						'default' => '',
					],
				],
			]
		);

		add_action( 'enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ] );
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
