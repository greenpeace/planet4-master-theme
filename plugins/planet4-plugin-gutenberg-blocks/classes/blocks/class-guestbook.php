<?php
/**
 * GuestBook block class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class GuestBook
 *
 * @package P4GBKS\Blocks
 */
class GuestBook extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'guestbook';

	/**
	 * GuestBook constructor.
	 */
	public function __construct() {
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
