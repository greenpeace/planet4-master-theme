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
	 * SplitTwoColumns constructor.
	 */
	public function __construct() {

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

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_editor_scripts' ] );
	}

	/**
	 * Load assets only on the admin pages of the plugin.
	 *
	 * @param string $hook The slug name of the current admin page.
	 */
	public function enqueue_editor_scripts( $hook ) {

		$option_values   = get_option( 'planet4_options' );
		$explore_page_id = $option_values['explore_page'] ?? '';
		// Variables exposed from PHP to JS,
		// WP calls this "localizing a script"...
		$reflection_vars = [
			'explore_page' => $explore_page_id,
		];
		wp_localize_script( 'planet4-blocks-script', 'p4ge_vars', $reflection_vars );
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
		$campaign_image_id = $fields['tag_image'] ?? get_term_meta( $tag_id, 'tag_attachment_id', true );
		$issue_image_id    = $fields['issue_image'] ?? get_post_thumbnail_id( $issue_id );

		$issue_title       = $fields['title'] ?? ( $issue_meta_data['p4_title'][0] ?? get_the_title( $issue_id ) );
		$issue_description = $fields['issue_description'] ?? ( $issue_meta_data['p4_description'][0] ?? '' );
		$issue_link_text   = $fields['issue_link_text'] ?? __( 'Learn more about this issue', 'planet4-blocks' );
		$issue_link_path   = $fields['issue_link_path'] ?? get_permalink( $issue_id );

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
				'name'        => $tag instanceof \WP_Error ? '' : html_entity_decode( $tag->name ),
				'link'        => get_tag_link( $tag ),
				'description' => $fields['tag_description'] ?? ( $tag instanceof \WP_Error ? '' : $tag->description ),
				'button_text' => $fields['button_text'] ?? __( 'Get Involved', 'planet4-blocks' ),
				'button_link' => $fields['button_link'] ?? get_tag_link( $tag ),
				'focus'       => $fields['focus_tag_image'] ?? '',
			],
		];

		return $data;
	}
}
