<?php
/**
 * Carousel Header block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class CarouselHeader
 * Registers planet4-blocks/carousel-header block.
 *
 * @package P4BKS
 * @since 0.1
 */
class CarouselHeader extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'carousel_header';

	/**
	 * Gallery constructor.
	 */
	public function __construct() {

		register_block_type(
			'planet4-blocks/carousel-header',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'block_style'       => [
						'type'    => 'string',
						'default' => 'zoom-and-slide-to-gray',
					],
					'carousel_autoplay' => [
						'type'    => 'string',
						'default' => '',
					],
					'slides'            => [
						'type'    => 'array',
						'default' => [],
						'items'   => [
							'type'       => 'object',
							// In JSON Schema you can specify object properties in the properties attribute.
							'properties' => [
								'image'            => [
									'type' => 'integer',
								],
								'header'           => [
									'type' => 'string',
								],
								'header_size'      => [
									'type' => 'string',
								],
								'subheader'        => [
									'type' => 'string',
								],
								'description'      => [
									'type' => 'string',
								],
								'link_text'        => [
									'type' => 'string',
								],
								'link_url'         => [
									'type' => 'string',
								],
								'focal_points'     => [
									'type' => 'object',
								],
								'link_url_new_tab' => [
									'type' => 'boolean',
								],
							],
						],
					],
				],
			]
		);

		add_action( 'enqueue_block_editor_assets', [ $this, 'enqueue_editor_scripts' ] );
	}

	/**
	 * Enqueue required scripts for the editor.
	 */
	public function enqueue_editor_scripts() {
		wp_enqueue_script( 'hammer', 'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js', [], '2.0.8', true );
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $fields ): array {
		if ( ! isset( $fields['block_style'] ) || empty( $fields['block_style'] ) ) {
			$fields['block_style'] = 'zoom-and-slide-to-gray';
		}

		$total_images = 0;
		if ( ! empty( $fields['slides'] ) ) {
			foreach ( $fields['slides'] as &$slide ) {
				$image_id   = $slide['image'];
				$temp_array = wp_get_attachment_image_src( $image_id, 'retina-large' );
				if ( false !== $temp_array && ! empty( $temp_array ) ) {
					$slide['image']        = $temp_array[0];
					$slide['image_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', wp_get_attachment_metadata( $image_id ) );
					$slide['image_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
					$total_images ++;
				}

				if ( isset( $slide['focal_points'] ) ) {

					$x = isset( $slide['focal_points']['x'] ) ? round( $slide['focal_points']['x'] * 100, 0 ) . '% ' : '50%';
					$y = isset( $slide['focal_points']['y'] ) ? round( $slide['focal_points']['y'] * 100, 0 ) . '% ' : '50%';

					$focus_image          = "$x $y";
					$slide['focus_image'] = $focus_image;
				}
				$temp_image         = wp_prepare_attachment_for_js( $image_id );
				$slide['image_alt'] = $temp_image['alt'] ?? '';

			}
		}
		$fields['total_images'] = $total_images;

		// Enqueue js for the frontend.
		if ( ! $this->is_rest_request() ) {
			wp_enqueue_script( 'hammer', 'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js', [], '2.0.8', true );
			wp_enqueue_script(
				'carousel-header',
				P4GBKS_PLUGIN_URL . 'assets/build/carouselHeaderFrontIndex.js',
				[
					'jquery',
					'hammer',
				],
				'0.3',
				true
			);
		}

		$block_data = [
			'fields' => $fields,
		];

		return $block_data;
	}
}
