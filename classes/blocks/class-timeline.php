<?php
/**
 * Timeline block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

use WP_Block_Type_Registry;

/**
 * Class Timeline
 *
 * @package P4GBKS\Blocks
 * @since 0.1
 */
class Timeline extends Base_Block {

	/** @const string BLOCK_NAME */
	public const BLOCK_NAME = 'timeline';

	/** @const string TIMELINE_JS_VERSION */
	public const TIMELINE_JS_VERSION = '3.8.12';

	/**
	 * Timeline constructor.
	 */
	public function __construct() {
		if ( WP_Block_Type_Registry::get_instance()->is_registered( self::get_full_block_name() ) ) {
			return;
		}

		$this->register_timeline_block();
	}

	/**
	 * Register block
	 */
	public function register_timeline_block() {
		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			self::get_full_block_name(),
			[
				'editor_script'   => 'planet4-blocks',
				// todo: Remove when all content is migrated.
				'render_callback' => [ self::class, 'hydrate_frontend' ],
				'attributes'      => [
					'timeline_title'    => [
						'type'    => 'string',
						'default' => '',
					],
					'description'       => [
						'type'    => 'string',
						'default' => '',
					],
					'google_sheets_url' => [
						'type'    => 'string',
						'default' => '',
					],
					'language'          => [
						'type'    => 'string',
						'default' => 'en',
					],
					'timenav_position'  => [
						'type'    => 'string',
						'default' => 'bottom',
					],
					'start_at_end'      => [
						'type'    => 'boolean',
						'default' => false,
					],
				],
			]
		);

		wp_register_script(
			'timeline-js',
			'https://cdn.knightlab.com/libs/timeline3/' . self::TIMELINE_JS_VERSION . '/js/timeline-min.js',
			[],
			self::TIMELINE_JS_VERSION,
			true
		);

		wp_register_style(
			'timeline-css',
			'https://cdn.knightlab.com/libs/timeline3/' . self::TIMELINE_JS_VERSION . '/css/timeline.css',
			[],
			self::TIMELINE_JS_VERSION
		);

		add_action( 'enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ] );
	}

	/**
	 * Frontend script
	 */
	public static function enqueue_frontend_script(): void {
		wp_enqueue_script(
			static::get_full_block_name() . '-script',
			static::get_url_path() . 'Script.js',
			[
				'planet4-blocks-script',
				'timeline-js',
			],
			\P4GBKS\Loader::file_ver( static::get_dir_path() . 'Script.js' ),
			true
		);
	}

	/**
	 * Frontend style
	 */
	public static function enqueue_frontend_style(): void {
		wp_enqueue_style(
			static::get_full_block_name() . '-style',
			static::get_url_path() . 'Style.min.css',
			[ 'timeline-css' ],
			\P4GBKS\Loader::file_ver( static::get_dir_path() . 'Style.min.css' ),
		);
	}

	/**
	 * Required by Base_Block.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {
		return [];
	}
}
