<?php
/**
 * ActionPageDummy block class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class ActionPageDummy
 *
 * @package P4GBKS\Blocks
 */
class ActionPageDummy extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'action-page-dummy';

	/**
	 * ActionPageDummy constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_actionpagedummy_block' ] );
	}

	/**
	 * Register ActionPageDummy block.
	 */
	public function register_actionpagedummy_block() {
		register_block_type(
			self::get_full_block_name(),
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
