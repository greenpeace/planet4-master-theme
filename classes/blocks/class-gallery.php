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
			'planet4-blocks/gallery',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'gallery_block_style'        => [
						'type'    => 'integer',
						'default' => 1,
					],
					'gallery_block_title'        => [
						'type' => 'string',
					],
					'gallery_block_description'  => [
						'type' => 'string',
					],
					'multiple_image'             => [
						'type' => 'string',
					],
					'gallery_block_focus_points' => [
						'type' => 'string',
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

		$gallery_style       = $fields['gallery_block_style'] ?? static::LAYOUT_SLIDER;
		$gallery_title       = $fields['gallery_block_title'] ?? '';
		$gallery_description = $fields['gallery_block_description'] ?? '';
		$images              = [];

		if ( isset( $fields['multiple_image'] ) ) {
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
			$image_size = $image_sizes[ $fields['gallery_block_style'] ];
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

		$gallery_id = 'gallery_' . uniqid();

		$post_type = get_post_type();

		$data = [
			'id'          => $gallery_id,
			'layout'      => $gallery_style,
			'title'       => $gallery_title,
			'description' => $gallery_description,
			'images'      => $images,
			'post_type'   => $post_type,
		];

		return $data;
	}
}
