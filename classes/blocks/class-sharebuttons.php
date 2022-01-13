<?php
/**
 * Hubspot Forms block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class ShareButtons
 * Registers the ShareButtons block.
 *
 * @package P4BKS
 * @since 0.1
 */
class ShareButtons extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'share-buttons';

	/**
	 * ShareButtons constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_sharebuttons_block' ] );
	}

	/**
	 * Block attributes.
	 *
	 * @var array $attributes Block attributes definition.
	 */
	private static $attributes = [
		'url'             => [
			'type' => 'string',
		],
		'gaEvent'         => [
			'type' => 'string',
		],
		'gaEventCategory' => [
			'type' => 'string',
		],
		'gaCategory'      => [
			'type' => 'string',
		],
		'gaAction'        => [
			'type' => 'string',
		],
		'gaLabel'         => [
			'type' => 'string',
		],
		'utmMedium'       => [
			'type' => 'string',
		],
		'utmContent'      => [
			'type' => 'string',
		],
		'utmCampaign'     => [
			'type' => 'string',
		],
		'email'           => [
			'type'       => 'object',
			'properties' => [
				'title'        => [
					'type'    => 'string',
					'default' => '',
				],
				'body'         => [
					'type'    => 'string',
					'default' => '',
				],
				'showInMenu'   => [
					'type'    => 'boolean',
					'default' => true,
				],
				'openInNewTab' => [
					'type'    => 'boolean',
					'default' => true,
				],
			],
		],
		'whatsapp'        => [
			'type'       => 'object',
			'properties' => [
				'baseSharedUrl' => [
					'type'    => 'string',
					'default' => 'https://wa.me',
				],
				'showInMenu'    => [
					'type'    => 'boolean',
					'default' => true,
				],
				'openInNewTab'  => [
					'type'    => 'boolean',
					'default' => true,
				],
			],
		],
		'facebook'        => [
			'type'       => 'object',
			'properties' => [
				'baseSharedUrl' => [
					'type'    => 'string',
					'default' => 'https://www.facebook.com/sharer/sharer.php',
				],
				'showInMenu'    => [
					'type'    => 'boolean',
					'default' => true,
				],
				'openInNewTab'  => [
					'type'    => 'boolean',
					'default' => true,
				],
			],
		],
		'twitter'         => [
			'type'       => 'object',
			'properties' => [
				'baseSharedUrl' => [
					'type'    => 'string',
					'default' => 'https://twitter.com/share',
				],
				'showInMenu'    => [
					'type'    => 'boolean',
					'default' => true,
				],
				'openInNewTab'  => [
					'type'    => 'boolean',
					'default' => true,
				],
				'text'          => [
					'type'    => 'string',
					'default' => '',
				],
				'description'   => [
					'type'    => 'string',
					'default' => '',
				],
				'account'       => [
					'type'    => 'string',
					'default' => '',
				],
			],
		],
		'version'         => [
			'type' => 'integer',
		],
	];

	/**
	 * Register ShareButtons block.
	 */
	public function register_sharebuttons_block() {
		register_block_type(
			self::get_full_block_name(),
			[
				'render_callback' => [ $this, 'front_end_rendered_fallback' ],
				'attributes'      => static::$attributes,
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
