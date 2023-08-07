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
	const BLOCK_NAME = 'carousel-header';

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
		register_block_type(
			'planet4-blocks/carousel-header',
			[
				'render_callback' => [ $this, 'front_end_rendered_fallback' ],
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
									'type' => 'string',
								],
								'image_srcset'     => [
									'type' => 'string',
								],
								'image_alt'        => [
									'type' => 'string',
								],
								'header'           => [
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
		add_action(
			'wp_enqueue_scripts',
			static function () {
				if ( has_block( 'planet4-blocks/carousel-header' ) ) {
					wp_enqueue_script(
						'hammer',
						'https://cdnjs.cloudflare.com/ajax/libs/hammer.js/2.0.8/hammer.min.js',
						[],
						'2.0.8',
						true
					);
				}
			}
		);
		add_action( 'wp_enqueue_scripts', [ self::class, 'enqueue_frontend_assets' ] );
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
	 * If the content is not empty, it's the new version and doesn't need any back end rendering.
	 * Otherwise, it means the block was not migrated in the editor yet. Fall back to front end rendering from scratch.
	 *
	 * @param array  $attributes Attributes of the block.
	 * @param string $content Content of the block.
	 *
	 * @return string The block's content string.
	 */
	public function front_end_rendered_fallback( $attributes, $content ) {
		if ( ! empty( $content ) ) {
			return $content;
		}

		if ( ! empty( $attributes['slides'] ) && empty( $attributes['slides'][0]['image_url'] ) ) {
			$attributes['slides'] = self::get_slides_image_data( $attributes['slides'] );
		}

		$json = wp_json_encode( [ 'attributes' => $attributes ] );

		// Render the block using a regular front end rendered, until the block data was migrated in the editor. For
		// production sites we will do this for all occurrences of the block, right after deploy.
		return '<div data-render="planet4-blocks/carousel-header" data-attributes="' . htmlspecialchars( $json ) . '">'
		. '</div>';
	}

	/**
	 * Get image data for the slides.
	 *
	 * @param array $slides Slides of this block.
	 *
	 * @return array The image data to be passed in the View.
	 */
	private static function get_slides_image_data( $slides ): array {
		if ( ! empty( $slides ) ) {
			foreach ( $slides as &$slide ) {
				try {
					$image_id   = $slide['image'];
					$temp_array = wp_get_attachment_image_src( $image_id, 'retina-large' );
					if ( false !== $temp_array && ! empty( $temp_array ) ) {
						$slide['image_url']    = $temp_array[0];
						$slide['image_srcset'] = wp_get_attachment_image_srcset( $image_id, 'retina-large', wp_get_attachment_metadata( $image_id ) );
					}

					$temp_image         = wp_prepare_attachment_for_js( $image_id );
					$slide['image_alt'] = $temp_image['alt'] ?? '';
				} catch ( \Exception $e ) {
					function_exists( '\Sentry\captureException' ) && \Sentry\captureException( $e );
				}
			}
		}

		return $slides;
	}
}
