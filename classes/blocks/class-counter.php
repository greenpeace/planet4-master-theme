<?php
/**
 * Counter block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

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
	 * @param array  $attributes Block attributes.
	 * @param string $content    Block content.
	 *
	 * @return mixed
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$attributes = shortcode_atts(
			[
				'title'         => 1,
				'description'   => '',
				'style'         => 'plain',
				'completed'     => 0,
				'completed_api' => '',
				'target'        => 0,
				'text'          => '',
			],
			$attributes,
			'shortcake_counter'
		);

		return $this->render( $attributes );
	}

	/**
	 * Counter constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_counter', [ $this, 'add_block_shortcode' ] );

		register_block_type(
			'planet4-blocks/counter',
			[  // - Register the block for the editor
				'editor_script'   => 'planet4-blocks',  // in the PHP side.
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'title'         => [
						'type'    => 'string',
						'default' => '',
					],
					'description'   => [
						'type'    => 'string',
						'default' => '',
					],
					'style'         => [
						'type'    => 'string',
						'default' => 'plain',
					],
					'completed'     => [
						'type'    => 'integer',
						'default' => 0,
					],
					'completed_api' => [
						'type' => 'string',
					],
					'target'        => [
						'type'    => 'integer',
						'default' => 0,
					],
					'text'          => [
						'type'    => 'text',
						'default' => '',
					],
				],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of the block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $fields ) : array {

		$completed = 0;
		if ( array_key_exists( 'completed', $fields ) ) {
			$completed = floatval( $fields['completed'] );
		}
		$target = floatval( $fields['target'] );

		if ( ! empty( $fields['completed_api'] ) ) {
			$response_api  = wp_safe_remote_get( $fields['completed_api'] );
			$response_body = json_decode( $response_api['body'], true );
			if ( is_array( $response_body ) && array_key_exists( 'unique_count', $response_body ) && is_int( $response_body['unique_count'] ) ) {
				$completed = floatval( $response_body['unique_count'] );
			}
		}

		$fields['completed'] = $completed;
		$fields['percent']   = $target > 0 ? round( $completed / $target * 100 ) : 0;

		$remaining           = $target > $completed ? $target - $completed : 0;
		$fields['remaining'] = floatval( $remaining );

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			wp_enqueue_script( 'counter', P4GBKS_PLUGIN_URL . 'public/js/counter.js', [ 'jquery' ], '0.1', true );
		}

		return [
			'fields' => $fields,
		];
	}
}
