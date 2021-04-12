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
 * Registers the CarouselHeader block.
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
	const BLOCK_NAME = 'carousel-header-beta';

	/**
	 * CarouselHeader constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_carouselheader_block' ] );
	}

	/**
	 * Register CarouselHeader block.
	 */
	public function register_carouselheader_block() {
		wp_enqueue_script( 'hammer', 'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js', [], '2.0.8', true );

		register_block_type(
			self::get_full_block_name(),
			[
				'apiVersion'      => 2,
				'render_callback' => [ $this, 'render_hydratable' ],
				'attributes'      => [
					'carousel_autoplay' => [
						'type'    => 'boolean',
						'default' => false,
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
								'image_url'        => [
									'type' => 'integer',
								],
								'image_srcset'     => [
									'type' => 'integer',
								],
								'image_sizes'      => [
									'type' => 'integer',
								],
								'image_alt'        => [
									'type' => 'integer',
								],
								'header'           => [
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

		add_action( 'enqueue_block_editor_assets', [ self::class, 'enqueue_editor_assets' ] );
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ] );
	}

	/**
	 * Render CarouselHeader block as hydratable content.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 * @param array $content This is the content of this block.
	 */
	public function render_hydratable( $attributes, $content ) {
		if ( ! empty( $attributes['slides'] ) && empty( $attributes['slides'][0]['image_url'] ) ) {
			$attributes['slides'] = $this->prepare_data( $attributes['slides'] );
		}

		if ( is_string( $content ) && trim( $content ) === '' ) {
			$content = self::convert_to_static_block( $attributes );
		}

		return self::as_hydratable_block( $attributes, $content );
	}

	/**
	 * Convert CarouselHeader to static block.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 */
	public static function convert_to_static_block( $attributes ) {
		$node_script     = 'assets/build/CarouselHeaderMigrate-server.js';
		$blocks_dir      = P4GBKS_PLUGIN_DIR;
		$attributes_json = wp_json_encode( $attributes );

		exec( "cd ${blocks_dir} && node ${node_script} '${attributes_json}' 2>&1", $out, $err );

		return $out[0];
	}

	/**
	 * Get image data.
	 *
	 * @param array $slides Slides of this block.
	 *
	 * @return array The image data to be passed in the View.
	 */
	public function prepare_data( $slides ): array {
		if ( ! empty( $slides ) ) {
			foreach ( $slides as &$slide ) {
				$image_id   = $slide['image'];
				$temp_array = wp_get_attachment_image_src( $image_id, 'retina-large' );
				if ( false !== $temp_array && ! empty( $temp_array ) ) {
					$slide['image_url']    = $temp_array[0];
					$slide['image_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', wp_get_attachment_metadata( $image_id ) );
					$slide['image_sizes']  = wp_calculate_image_sizes( 'retina-large', null, null, $image_id );
				}

				$temp_image         = wp_prepare_attachment_for_js( $image_id );
				$slide['image_alt'] = $temp_image['alt'] ?? '';
			}
		}

		return $slides;
	}
}
