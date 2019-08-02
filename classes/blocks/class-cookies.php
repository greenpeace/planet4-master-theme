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
	 * Register old shortcode for backwarsd compatibility.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @param array $content The content of the post.
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$attributes = shortcode_atts(
			[
				'title'                         => '',
				'description'                   => '',
				'necessary_cookies_name'        => '',
				'necessary_cookies_description' => '',
				'all_cookies_name'              => '',
				'all_cookies_description'       => '',
			],
			$attributes,
			'shortcake_cookies'
		);

		return $this->render( $attributes );
	}

	/**
	 * Cookies constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_cookies', [ $this, 'add_block_shortcode' ] );

		// - Register the block for the editor
		// in the PHP side.
		register_block_type(
			'planet4-blocks/cookies',
			[
				'editor_script'   => 'planet4-blocks/cookies',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'title'                         => [
						'type'    => 'string',
						'default' => '',
					],
					'description'                   => [
						'type'    => 'string',
						'default' => '',
					],
					'necessary_cookies_name'        => [
						'type'    => 'string',
						'default' => '',
					],
					'necessary_cookies_description' => [
						'type'    => 'string',
						'default' => '',
					],
					'all_cookies_name'              => [
						'type'    => 'string',
						'default' => '',
					],
					'all_cookies_description'       => [
						'type'    => 'string',
						'default' => '',
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
			wp_enqueue_script( 'cookies', P4GBKS_PLUGIN_URL . 'public/js/cookies.js', [ 'jquery' ], '0.1', true );
		}

		$data = [
			'title'                         => $attributes['title'] ?? '',
			'description'                   => $attributes['description'] ?? '',
			'necessary_cookies_name'        => $attributes['necessary_cookies_name'] ?? '',
			'necessary_cookies_description' => $attributes['necessary_cookies_description'] ?? '',
			'all_cookies_name'              => $attributes['all_cookies_name'] ?? '',
			'all_cookies_description'       => $attributes['all_cookies_description'] ?? '',
		];

		return $data;
	}
}
