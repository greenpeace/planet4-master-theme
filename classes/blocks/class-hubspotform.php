<?php
/**
 * Hubspot Forms block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class HubspotForm
 * Registers the HubspotForm block.
 *
 * @package P4BKS
 * @since 0.1
 */
class HubspotForm extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'hubspot-form';

	/**
	 * HubspotForm constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_hubspotform_block' ] );
	}

	/**
	 * Register HubspotForm block.
	 */
	public function register_hubspotform_block() {
		register_block_type(
			self::get_full_block_name(),
			[
				'render_callback' => [ $this, 'front_end_rendered_fallback' ],
				'attributes'      => [
					'blockTitle'                         => [
						'type' => 'string',
					],
					'blockText'                          => [
						'type' => 'string',
					],
					'blockBackgroundImageId'             => [
						'type' => 'string',
					],
					'blockBackgroundImageUrl'            => [
						'type' => 'string',
					],
					'blockStyle'                         => [
						'type' => 'string',
					],
					'ctaText'                            => [
						'type' => 'string',
					],
					'ctaLink'                            => [
						'type' => 'string',
					],
					'ctaNewTab'                          => [
						'type'    => 'boolean',
						'default' => false,
					],
					'formTitle'                          => [
						'type' => 'string',
					],
					'formText'                           => [
						'type' => 'string',
					],
					'hubspotShortcode'                   => [
						'type' => 'string',
					],
					'hubspotThankyouMessage'             => [
						'type' => 'string',
					],
					'enableCustomHubspotThankyouMessage' => [
						'type'    => 'boolean',
						'default' => true,
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
