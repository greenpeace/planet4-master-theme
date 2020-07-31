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
				// todo: Remove when all content is migrated.
				'render_callback' => static function ( $attributes ) {
					$json = wp_json_encode( [ 'attributes' => $attributes ] );
					return '<div data-render="planet4-blocks/happypoint" data-attributes="' . htmlspecialchars( $json ) . '"></div>';
				},
				'attributes'      => [
					'id'                  => [
						'type' => 'integer',
					],
					'focus_image'         => [
						'type' => 'string',
					],
					'opacity'             => [
						'type'    => 'integer',
						'default' => 30,
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
	 * Required by the `Base_Block` class.
	 *
	 * @param array $fields Unused, required by the abstract function.
	 */
	public function prepare_data( $fields ): array {
		return [];
	}

	/**
	 * Get the required data for the frontend.
	 *
	 * @param integer $id Image id.
	 */
	public static function get_data( $id ) {
		$data                        = [];
		$options                     = get_option( 'planet4_options' );
		$p4_happy_point_bg_image     = $options['happy_point_bg_image_id'] ?? '';
		$image_id                    = $id ? $id : $p4_happy_point_bg_image;
		$img_meta                    = wp_get_attachment_metadata( $image_id );
		$image_alt                   = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
		$data['background_src']      = wp_get_attachment_image_src( $image_id, 'retina-large' );
		$data['background_srcset']   = wp_get_attachment_image_srcset( $image_id, 'retina-large', $img_meta );
		$data['background_sizes']    = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
		$data['engaging_network_id'] = $options['engaging_network_form_id'] ?? '';
		$data['default_image']       = get_bloginfo( 'template_directory' ) . '/images/happy-point-block-bg.jpg';
		$data['background_alt']      = empty( $image_alt ) ? __( 'Background image', 'planet4-blocks' ) : $image_alt;

		return $data;
	}
}
