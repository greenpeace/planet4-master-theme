<?php
/**
 * Split Two Columns block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class SplitTwoColumns
 *
 * @package P4BKS
 * @since 0.1
 */
class SplitTwoColumns extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'split_two_columns';

	/**
	 * Register shortcake shortcode.
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param string $content   Content.
	 *
	 * @return mixed
	 */
	public function add_block_shortcode( $attributes, $content ) {

		$attributes = shortcode_atts(
			[
				'select_issue'      => '',
				'title'             => '',
				'issue_description' => '',
				'issue_link_text'   => '',
				'issue_link_path'   => '',
				'issue_image'       => '',
				'focus_issue_image' => '',
				'select_tag'        => '',
				'tag_description'   => '',
				'button_text'       => '',
				'button_link'       => '',
				'tag_image'         => '',
				'focus_tag_image'   => '',
			],
			$attributes,
			'shortcake_split_two_columns'
		);

		return $this->render( $attributes );
	}


	/**
	 * SplitTwoColumns constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_split_two_columns', [ $this, 'add_block_shortcode' ] );

		register_block_type(
			'planet4-blocks/split-two-columns',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'select_issue'      => [
						'type' => 'integer',
					],
					'title'             => [
						'type' => 'string',
					],
					'issue_description' => [
						'type' => 'string',
					],
					'issue_link_text'   => [
						'type' => 'string',
					],
					'issue_link_path'   => [
						'type' => 'string',
					],
					'issue_image'       => [
						'type' => 'integer',
					],
					'focus_issue_image' => [
						'type' => 'string',
					],
					'select_tag'        => [
						'type' => 'integer',
					],
					'tag_description'   => [
						'type' => 'string',
					],
					'button_text'       => [
						'type' => 'string',
					],
					'button_link'       => [
						'type' => 'string',
					],
					'tag_image'         => [
						'type' => 'integer',
					],
					'focus_tag_image'   => [
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
		$issue_id        = absint( $fields['select_issue'] ?? '' );
		$issue_meta_data = get_post_meta( $issue_id );

		$tag_id            = absint( $fields['select_tag'] ?? '' );
		$tag               = get_term( $tag_id );
		$campaign_image_id = ! empty( $fields['tag_image'] ) ? $fields['tag_image'] : get_term_meta( $tag_id, 'tag_attachment_id', true );
		$issue_image_id    = ! empty( $fields['issue_image'] ) ? $fields['issue_image'] : get_post_thumbnail_id( $issue_id );

		$issue_title       = empty( $fields['title'] ) ? ( $issue_meta_data['p4_title'][0] ?? get_the_title( $issue_id ) ) : $fields['title'];
		$issue_description = empty( $fields['issue_description'] ) ? ( $issue_meta_data['p4_description'][0] ?? '' ) : $fields['issue_description'];
		$issue_link_text   = empty( $fields['issue_link_text'] ) ? __( 'Learn more about this issue', 'planet4-blocks' ) : $fields['issue_link_text'];
		$issue_link_path   = empty( $fields['issue_link_path'] ) ? get_permalink( $issue_id ) : $fields['issue_link_path'];

		$data = [
			'issue'    => [
				'title'       => html_entity_decode( $issue_title ),
				'description' => $issue_description,
				'image'       => wp_get_attachment_url( $issue_image_id ),
				'srcset'      => wp_get_attachment_image_srcset( $issue_image_id ),
				'image_alt'   => get_post_meta( $issue_image_id, '_wp_attachment_image_alt', true ),
				'link_text'   => $issue_link_text,
				'link_url'    => $issue_link_path,
				'focus'       => $fields['focus_issue_image'] ?? '',
			],
			'campaign' => [
				'image'       => wp_get_attachment_url( $campaign_image_id ),
				'srcset'      => wp_get_attachment_image_srcset( $campaign_image_id, 'large' ),
				'image_alt'   => get_post_meta( $campaign_image_id, '_wp_attachment_image_alt', true ),
				'name'        => ( ! is_object( $tag ) || $tag instanceof \WP_Error ) ? '' : html_entity_decode( $tag->name ),
				'link'        => get_tag_link( $tag ),
				'description' =>
					! empty( $fields['tag_description'] )
						? $fields['tag_description']
						: $tag->description ?? '',
				'button_text' => $fields['button_text'] ?? __( 'Get Involved', 'planet4-blocks' ),
				'button_link' => $fields['button_link'] ?? get_tag_link( $tag ),
				'focus'       => $fields['focus_tag_image'] ?? '',
			],
		];

		return $data;
	}
}
