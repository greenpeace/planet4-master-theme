<?php
/**
 * Hubspot Forms block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class WhatsappShare
 * Registers the WhatsappShare block.
 *
 * @package P4BKS
 * @since 0.1
 */
class WhatsappShare extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'whatsapp-share';

	/**
	 * WhatsappShare constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_whatsappshare_block' ] );
	}

	/**
	 * Register whatsappshare block.
	 */
	public function register_whatsappshare_block() {
		register_block_type(
			self::get_full_block_name(),
			[
				'render_callback' => [ $this, 'front_end_rendered_fallback' ],
				'attributes'      => [ ],
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
