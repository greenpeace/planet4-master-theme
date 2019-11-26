<?php
/**
 * Socialshare block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Socialshare_Controller
 *
 * @package P4BKS
 * @since 0.1
 */
class Socialshare extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'social_share';

	/**
	 * Register shortcake shortcode.
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param string $content   Content.
	 *
	 * @return mixed
	 */
	public function add_block_shortcode( $attributes, $content ) {

		$attributes['id'] = $attributes['background'];

		$attributes = shortcode_atts(
			[
				'title'                      => '',
				'description'                => '',
				'id'                         => '',
				'gallery_block_focus_points' => '',
				'urls'                       => '',
				'multiple_image'             => '',
				'messages'                   => '',
			],
			$attributes,
			'shortcake_social_share'
		);

		return $this->render( $attributes );
	}

	/**
	 * Socialshare constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_social_share', [ $this, 'add_block_shortcode' ] );

		register_block_type(
			'planet4-blocks/socialshare',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'id'                         => [
						'type' => 'integer',
					],
					'title'                      => [
						'type'    => 'string',
						'default' => '',
					],
					'description'                => [
						'type'    => 'string',
						'default' => '',
					],
					'gallery_block_focus_points' => [
						'type' => 'string',
					],
					'urls'                       => [
						'type' => 'string',
					],
					'messages'                   => [
						'type' => 'string',
					],
					'multiple_image'             => [
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

		$images = [];

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

		if ( isset( $fields['messages'] ) ) {
			$messages = json_decode( str_replace( "'", '"', $fields['messages'] ), true );
		} else {
			$messages = [];
		}

		if ( isset( $fields['urls'] ) ) {
			$urls = json_decode( str_replace( "'", '"', $fields['urls'] ), true );
		} else {
			$urls = [];
		}

		$images_dimensions = [];

		$fields['id'] = $fields['id'] ?? '';

		$count = 0;
		foreach ( $exploded_images as $image_id ) {
			$image_size = 'retina-large';
			$image_data = [];

			$image_data_array           = wp_get_attachment_image_src( $image_id, $image_size );
			$image_data['image_src']    = $image_data_array ? $image_data_array[0] : '';
			$image_data['image_srcset'] = wp_get_attachment_image_srcset( $image_id, $image_size, wp_get_attachment_metadata( $image_id ) );
			$image_data['image_sizes']  = wp_calculate_image_sizes( $image_size, null, null, $image_id );
			$image_data['alt_text']     = get_post_meta( $image_id, '_wp_attachment_image_alt', true );
			$image_data['caption']      = wp_get_attachment_caption( $image_id );
			$image_data['focus_image']  = $img_focus_points[ $image_id ] ?? '';
			$image_data['message']      = $fields['image_data'][ $count ]['message'] ?? '';
			$image_data['social_url']   = $fields['image_data'][ $count ]['social_url'] ?? '';
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
			$count++;
		}
		$fields['title']       = '' !== $fields['title'] ? $fields['title'] : '';
		$fields['description'] = '' !== $fields['description'] ? $fields['description'] : '';
		$data                  = [
			'fields' => $fields,
			'images' => $images,
		];

		return $data;
	}
}
