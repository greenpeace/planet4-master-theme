<?php
/**
 * Base block class.
 *
 * @package P4GEN
 */

namespace P4GEN\Blocks;

/**
 * Class Base_Block
 *
 * @package P4GEN\Blocks
 */
class Base_Block {

	/**
	 * @param array $attributes Block attributes.
	 *
	 * @return mixed
	 */
	public function render( $attributes ) {
		$data = $this->prepare_data( $attributes );

		\Timber::$locations = P4GEN_PLUGIN_DIR . '/templates/blocks';

		$block_output = \Timber::compile( static::BLOCK_NAME . '.twig', $data );

		// Return empty string if rendered output contains only whitespace or new lines.
		// If it is a rest request from editor/admin area, return a message that block has no content.
		$empty_content = $this->is_rest_request() ? 'Block content is empty. Check the block\'s settings or remove it.' : '';

		return ctype_space( $block_output ) ? $empty_content : $block_output;
	}

	/**
	 * Outputs an error message.
	 *
	 * @param string $message Error message.
	 */
	public function render_error_message( $message ) {
		// Ensure only editors see the error, not visitors to the website.
		if ( current_user_can( 'edit_posts' ) ) {
			\Timber::render(
				P4GEN_PLUGIN_DIR . 'templates/block-error-message.twig',
				array(
					'category' => __( 'Error', 'planet4-gutenberg-engagingnetworks' ),
					'message'  => $message,
				)
			);
		}
	}

	/**
	 * Returns if current request is a rest api request.
	 *
	 * @return bool
	 */
	protected function is_rest_request() {
		if ( defined( 'REST_REQUEST' ) && REST_REQUEST ) {
			return true;
		}
		return false;
	}
}
