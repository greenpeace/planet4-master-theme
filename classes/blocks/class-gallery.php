<?php
/**
 * Gallery block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Gallery_Controller
 *
 * @package P4BKS
 * @since 0.1
 */
class Gallery extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'gallery';

	/**
	 * Allowed Post types.
	 *
	 * @const array BLOCK_ALLOWED_POST_TYPES.
	 */
	const BLOCK_ALLOWED_POST_TYPES = [ 'page', 'campaign', 'post' ];

	const LAYOUT_SLIDER        = 1;
	const LAYOUT_THREE_COLUMNS = 2;
	const LAYOUT_GRID          = 3;

	/**
	 * Gallery constructor.
	 */
	public function __construct() {
		register_block_type(
			self::get_full_block_name(),
			[
				'editor_script'   => 'planet4-blocks',
				// todo: Remove when all content is migrated.
				'render_callback' => static function ( $attributes ) {
					$attributes['images'] = self::get_images( $attributes );

					return self::render_frontend( $attributes );
				},
				'attributes'      => [
					'gallery_block_style'        => [ // Needed for existing blocks conversion.
						'type'    => 'integer',
						'default' => 0,
					],
					'gallery_block_title'        => [
						'type'    => 'string',
						'default' => '',
					],
					'gallery_block_description'  => [
						'type'    => 'string',
						'default' => '',
					],
					'multiple_image'             => [
						'type'    => 'string',
						'default' => '',
					],
					'gallery_block_focus_points' => [
						'type'    => 'string',
						'default' => '',
					],
					'image_data'                 => [
						'type'    => 'array',
						'default' => [],
						'items'   => [
							'type'       => 'object',
							'properties' => [
								'id'         => [
									'type' => 'integer',
								],
								'url'        => [
									'type' => 'string',
								],
								'focalPoint' => [
									'type' => 'object',
								],
							],
						],
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
	 * Get the images data that will be needed to render the block correctly.
	 *
	 * @param array $fields This is the array of fields of this block.
	 *
	 * @return array The images to be passed in the View.
	 */
	public static function get_images( $fields ): array {
		$images = [];

		if ( isset( $fields['multiple_image'] ) && '' !== $fields['multiple_image'] ) {
			$exploded_images = explode( ',', $fields['multiple_image'] );
		} else {
			$exploded_images = [];
		}

		if ( isset( $fields['gallery_block_focus_points'] ) ) {
			$img_focus_points = json_decode( str_replace( "'", '"', $fields['gallery_block_focus_points'] ), true );
		} else {
			$img_focus_points = [];
		}

		$images_dimensions = [];
		$image_sizes       = [
			self::LAYOUT_SLIDER        => 'retina-large',
			self::LAYOUT_THREE_COLUMNS => 'medium_large',
			self::LAYOUT_GRID          => 'large',
		];

		foreach ( $exploded_images as $image_id ) {
			$image_size = $fields['gallery_image_size'] ?? (
				$fields['gallery_block_style'] ? $image_sizes[ $fields['gallery_block_style'] ] : null
			);

			$image_data = [];

			$image_data_array           = wp_get_attachment_image_src( $image_id, $image_size );
			$image_data['image_src']    = $image_data_array ? $image_data_array[0] : '';
			$image_data['image_srcset'] = wp_get_attachment_image_srcset( $image_id, $image_size, wp_get_attachment_metadata( $image_id ) );
			$image_data['image_sizes']  = wp_calculate_image_sizes( $image_size, null, null, $image_id );
			$image_data['alt_text']     = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			$image_data['caption']      = wp_get_attachment_caption( $image_id );
			$image_data['focus_image']  = $img_focus_points[ $image_id ] ?? '';
			$attachment_fields          = get_post_custom( $image_id );
			$image_data['credits']      = '';
			if ( isset( $attachment_fields['_credit_text'][0] ) && ! empty( $attachment_fields['_credit_text'][0] ) ) {
				$image_data['credits'] = $attachment_fields['_credit_text'][0];
				if ( ! is_numeric( strpos( $attachment_fields['_credit_text'][0], '©' ) ) ) {
					$image_data['credits'] = '© ' . $image_data['credits'];
				}
			}

			if ( count( (array) $image_data_array ) >= 3 ) {
				$images_dimensions[] = $image_data_array[1];
				$images_dimensions[] = $image_data_array[2];
			}

			$images[] = $image_data;
		}

		return $images;
	}
}
