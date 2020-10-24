<?php
/**
 * Timeline block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Timeline
 *
 * @package P4GBKS\Blocks
 * @since 0.1
 */
class Timeline extends Base_Block {

	/** @const string BLOCK_NAME */
	const BLOCK_NAME = 'timeline';

	/**
	 * Timeline constructor.
	 */
	public function __construct() {
		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			'planet4-blocks/timeline',
			[
				'editor_script'   => 'planet4-blocks',
				// todo: Remove when all content is migrated.
				'render_callback' => static function ( $attributes ) {
					$json = wp_json_encode( [ 'attributes' => $attributes ] );

					return '<div data-render="planet4-blocks/timeline" data-attributes="' . htmlspecialchars( $json ) . '"></div>';
				},
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
