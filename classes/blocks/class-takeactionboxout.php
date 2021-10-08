<?php
/**
 * TakeActionBoxout block class.
 *
 * @package P4GBKS
 */

namespace P4GBKS\Blocks;

/**
 * Class TakeActionBoxout
 *
 * @package P4GBKS\Blocks
 */
class TakeActionBoxout extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'take-action-boxout';

	/**
	 * TakeActionBoxout constructor.
	 */
	public function __construct() {
		add_action( 'init', [ $this, 'register_takeactionboxout_block' ] );
	}

	/**
	 * Register Take action boxout block.
	 */
	public function register_takeactionboxout_block() {
		register_block_type(
			self::get_full_block_name(),
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'take_action_page' => [
						'type' => 'integer',
					],
					'title'            => [
						'type' => 'string',
					],
					'excerpt'          => [
						'type' => 'string',
					],
					'link'             => [
						'type' => 'string',
					],
					'linkText'         => [
						'type' => 'string',
					],
					'newTab'           => [
						'type'    => 'boolean',
						'default' => false,
					],
					'tag_ids'          => [
						'type'  => 'array',
						'items' => [
							'type' => 'integer', // Array definitions require an item type.
						],
					],
					'imageId'          => [
						'type' => 'integer',
					],
					'imageUrl'         => [
						'type' => 'string',
					],
					'imageAlt'         => [
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

		$page_id = $fields['take_action_page'] ?? '';

		if ( empty( $page_id ) ) {
			$tag_ids = $fields['tag_ids'] ?? '';

			if ( empty( $tag_ids ) ) {
				$tags = [];
			} else {
				// Explode comma separated list of tag ids and get an array of \WP_Terms objects.
				$wp_tags = get_tags( [ 'include' => $tag_ids ] );

				if ( is_array( $wp_tags ) && $wp_tags ) {
					foreach ( $wp_tags as $wp_tag ) {
						$tags[] = [
							'name' => $wp_tag->name,
							'link' => get_tag_link( $wp_tag ),
						];
					}
				}
			}

			if ( isset( $fields['imageUrl'] ) ) {
				$src      = $fields['imageUrl'];
				$alt_text = $fields['imageAlt'] ?? '';
			} elseif ( ! empty( $fields['background_image'] ) ) {
				list( $src ) = wp_get_attachment_image_src( $fields['background_image'], 'large' );
				$alt_text    = get_post_meta( $fields['background_image'], '_wp_attachment_image_alt', true );
			}

			$block = [
				'campaigns' => $tags,
				'title'     => $fields['custom_title'] ?? $fields['title'] ?? '',
				'excerpt'   => $fields['custom_excerpt'] ?? $fields['excerpt'] ?? '',
				'link'      => $fields['custom_link'] ?? $fields['link'] ?? '',
				'new_tab'   => $fields['custom_link_new_tab'] ?? $fields['newTab'] ?? false,
				'link_text' => $fields['custom_link_text'] ?? $fields['linkText'] ?? '',
				'image'     => $src ?? '',
				'image_alt' => $alt_text ?? '',
			];

			$data = [
				'boxout' => $block,
			];
			return $data;
		}

		$args = [
			'p'           => (int) $page_id, // ID of a page, post.
			'post_type'   => 'any',
			'post_status' => 'publish',
		];

		// Try to find the page that the user selected.
		$query = new \WP_Query( $args );
		$page  = null;
		$tag   = null;

		if ( ! $query->have_posts() ) {
			return [];
		}

		// Populate the necessary fields for the block.
		$posts   = $query->get_posts();
		$page    = $posts[0];
		$wp_tags = wp_get_post_tags( $page->ID );
		$tags    = [];

		if ( is_array( $wp_tags ) && $wp_tags ) {
			foreach ( $wp_tags as $wp_tag ) {
				$tags[] = [
					'name' => $wp_tag->name,
					'link' => get_tag_link( $wp_tag ),
				];
			}
		}

		$options = get_option( 'planet4_options' );

		if ( has_post_thumbnail( $page ) ) {
			$image     = get_the_post_thumbnail_url( $page, 'large' );
			$img_id    = get_post_thumbnail_id( $page );
			$image_alt = get_post_meta( $img_id, '_wp_attachment_image_alt', true );
		}

		// Populate variables.
		$block = [
			'campaigns' => $tags ?? [],
			'title'     => null === $page ? '' : $page->post_title,
			'excerpt'   => null === $page ? '' : $page->post_excerpt,
			'link'      => null === $page ? '' : get_permalink( $page ),
			'new_tab'   => false,
			'link_text' => $options['take_action_covers_button_text'] ?? __( 'Take action', 'planet4-blocks' ),
			'image'     => $image ?? '',
			'image_alt' => $image_alt ?? '',
		];

		$data = [
			'boxout' => $block,
		];

		return $data;
	}
}
