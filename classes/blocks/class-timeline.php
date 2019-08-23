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

	/** @const string TIMELINEJS_VERSION */
	const TIMELINEJS_VERSION = '3.6.3';

	/**
	 * @var int - needed to allow multiple timelines in the same post.
	 */
	protected static $id = 1;

	/**
	 * Register old shortcode for backwarsd compatibility.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @param array $content The content of the post.
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$attributes = shortcode_atts(
			[
				'timeline_title'    => '',
				'description'       => '',
				'google_sheets_url' => '',
				'language'          => 'en',
				'timenav_position'  => 'bottom',
				'start_at_end'      => false,
			],
			$attributes,
			'shortcake_timeline'
		);
		return $this->render( $attributes );
	}

	/**
	 * Timeline constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_timeline', [ $this, 'add_block_shortcode' ] );

		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			'planet4-blocks/timeline',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
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
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {
		$timeline_id = 'timeline-' . self::$id;
		self::$id ++;

		$url = esc_url( $attributes['google_sheets_url'] );

		$options = wp_json_encode(
			[
				'timenav_position' => sanitize_text_field( $attributes['timenav_position'] ),
				'start_at_end'     => boolval( $attributes['start_at_end'] ),
				'language'         => sanitize_text_field( $attributes['language'] ),
			]
		);

		// If request is coming from backend rendering.
		if ( $this->is_rest_request() ) {
			$post_id = filter_input( INPUT_GET, 'post_id', FILTER_VALIDATE_INT );
			if ( $post_id > 0 ) {
				$post = get_post( $post_id );
			}
		} else {
			$post = get_queried_object();
		}

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			$css = 'https://cdn.knightlab.com/libs/timeline3/' . self::TIMELINEJS_VERSION . '/css/timeline.css';
			wp_enqueue_style( 'timelinejs', $css, [], self::TIMELINEJS_VERSION );

			$js = 'https://cdn.knightlab.com/libs/timeline3/' . self::TIMELINEJS_VERSION . '/js/timeline-min.js';
			wp_enqueue_script( 'timelinejs', $js, [], self::TIMELINEJS_VERSION, true );
			wp_add_inline_script( 'timelinejs', "new TL.Timeline('$timeline_id', '$url', $options);" );
		}

		$data = [
			'timeline_title' => $attributes['timeline_title'] ?? '',
			'description'    => $attributes['description'] ?? '',
			'timeline_id'    => $timeline_id ?? '',
		];

		return $data;
	}
}
