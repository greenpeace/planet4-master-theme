<?php
/**
 * Happypoint block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Happypoint_Controller
 *
 * @package P4BKS
 * @since 0.1
 */
class Happypoint extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'happy_point';

	/**
	 * Happypoint constructor.
	 */
	public function __construct() {
		register_block_type(
			'planet4-blocks/happypoint',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'id'                  => [
						'type' => 'integer',
					],
					'focus_image'         => [
						'type'    => 'string',
						'default' => '',
					],
					'opacity'             => [
						'type'    => 'integer',
						'default' => '',
					],
					'mailing_list_iframe' => [
						'type' => 'boolean',
					],
					'iframe_url'          => [
						'type'    => 'string',
						'default' => '',
					],
					'load_iframe'         => [
						'type'    => 'boolean',
						'default' => 'false',
					],
				],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $fields ): array {

		if ( ! is_numeric( $fields['opacity'] ) ) {
			$fields['opacity'] = 30;
		}

		$fields['load_iframe']         = $fields['load_iframe'] ?? 'false';
		$fields['focus_image']         = $fields['focus_image'] ?? 'center center';
		$fields['iframe_url']          = $fields['iframe_url'] ?? '';
		$fields['mailing_list_iframe'] = $fields['mailing_list_iframe'] ?? '';
		$fields['id']                  = $fields['id'] ?? '';

		// Handle delete Happy point image case.
		if ( -1 === $fields['id'] ) {
			$fields['id'] = '';
		}

		$opacity = number_format( ( $fields['opacity'] / 100 ), 1 );

		$options                       = get_option( 'planet4_options' );
		$p4_happy_point_bg_image       = $options['happy_point_bg_image_id'] ?? '';
		$image_id                      = '' !== $fields['id'] ? $fields['id'] : $p4_happy_point_bg_image;
		$img_meta                      = wp_get_attachment_metadata( $image_id );
		$image_alt                     = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$fields['background_src']      = wp_get_attachment_image_src( $image_id, 'retina-large' );
		$fields['background_srcset']   = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
		$fields['background_sizes']    = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
		$fields['engaging_network_id'] = $options['engaging_network_form_id'] ?? '';
		$fields['opacity']             = $opacity;
		$fields['default_image']       = get_bloginfo( 'template_directory' ) . '/images/happy-point-block-bg.jpg';
		$fields['background_alt']      = empty( $image_alt ) ? __( 'Background image', 'planet4-blocks' ) : $image_alt;

		$data = [
			'fields' => $fields,
		];

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			\P4GBKS\Loader::enqueue_local_script( 'happy-point', 'public/js/happy_point.js', [ 'jquery' ] );
		}

		return $data;
	}
}
