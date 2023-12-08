<?php
/**
 * Cookies block class
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

use WP_Block_Type_Registry;

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
		if ( WP_Block_Type_Registry::get_instance()->is_registered( self::get_full_block_name() ) ) {
			return;
		}

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
						'type' => 'string',
					],
					'necessary_cookies_description'  => [
						'type' => 'string',
					],
					'all_cookies_name'               => [
						'type' => 'string',
					],
					'all_cookies_description'        => [
						'type' => 'string',
					],
					'analytical_cookies_name'        => [
						'type' => 'string',
					],
					'analytical_cookies_description' => [
						'type' => 'string',
					],
				],
			]
		);

		add_action( 'enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ] );
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
