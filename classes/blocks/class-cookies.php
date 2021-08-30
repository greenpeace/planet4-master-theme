<?php
/**
 * Cookies block class
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

/**
 * Class Cookies
 *
 * @package P4GBKS\Blocks
 */
class Cookies extends Base_Block {

	/** @const string BLOCK_NAME */
	const BLOCK_NAME = 'cookies';

	/**
	 * Cookies constructor.
	 */
	public function __construct() {
		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			self::get_full_block_name(),
			[
				// todo: Remove when all content is migrated.
				'render_callback' => [ self::class, 'render_frontend' ],
				'attributes'      => [
					'title'                          => [
						'type'    => 'string',
						'default' => '',
					],
					'description'                    => [
						'type'    => 'string',
						'default' => '',
					],
					'necessary_cookies_name'         => [
						'type'    => 'string',
						'default' => '',
					],
					'necessary_cookies_description'  => [
						'type'    => 'string',
						'default' => '',
					],
					'all_cookies_name'               => [
						'type'    => 'string',
						'default' => '',
					],
					'all_cookies_description'        => [
						'type'    => 'string',
						'default' => '',
					],
					'analytical_cookies_name'        => [
						'type'    => 'string',
						'default' => '',
					],
					'analytical_cookies_description' => [
						'type'    => 'string',
						'default' => '',
					],
				],
			]
		);
	}

	/**
	 * Required by the parent abstract class.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {
		return [];
	}
}
