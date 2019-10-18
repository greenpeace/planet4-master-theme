<?php
/**
 * Columns block class
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Blocks;

/**
 * Class Columns_Controller
 *
 * @package P4BKS
 * @since 0.1
 */
class Columns extends Base_Block {

	/**
	 * Block name.
	 *
	 * @const string BLOCK_NAME.
	 */
	const BLOCK_NAME = 'columns';

	const TASK_TEMPLATE_NAME = 'tasks';

	const LAYOUT_NO_IMAGE = 'no_image';
	const LAYOUT_TASKS    = 'tasks';
	const LAYOUT_ICONS    = 'icons';
	const LAYOUT_IMAGES   = 'image';
	const MAX_COLUMNS     = 4;

	/**
	 * Register shortcake shortcode.
	 *
	 * @param array  $attributes Shortcode attributes.
	 * @param string $content   Content.
	 *
	 * @return mixed
	 */
	public function add_block_shortcode( $attributes, $content ) {
		$columns = [];
		for ( $i = 1; $i <= static::MAX_COLUMNS; $i++ ) {
			if ( ! empty( $attributes[ 'title_' . $i ] ) ) {
				$column = [
					'title'        => $attributes[ 'title_' . $i ] ?? '',
					'description'  => $attributes[ 'description_' . $i ] ?? '',
					'attachment'   => $attributes[ 'attachment_' . $i ] ?? '',
					'cta_text'     => $attributes[ 'cta_text_' . $i ] ?? '',
					'cta_link'     => $attributes[ 'link_' . $i ] ?? '',
					'link_new_tab' => $attributes[ 'link_new_tab_' . $i ] ?? '',
				];

				array_push( $columns, $column );
			}
		}

		$attributes['columns'] = $columns;

		$attributes = shortcode_atts(
			[
				'columns_block_style' => '',
				'columns_title'       => '',
				'columns_description' => '',
				'columns'             => [],
			],
			$attributes,
			'shortcake_columns'
		);

		return $this->render( $attributes );
	}

	/**
	 * Columns constructor.
	 */
	public function __construct() {
		add_shortcode( 'shortcake_columns', [ $this, 'add_block_shortcode' ] );

		register_block_type(
			'planet4-blocks/columns',
			[
				'editor_script'   => 'planet4-blocks',
				'render_callback' => [ $this, 'render' ],
				'attributes'      => [
					'columns_block_style' => [
						'type' => 'string',
					],
					'columns_title'       => [
						'type' => 'string',
					],
					'columns_description' => [
						'type' => 'string',
					],
					'columns'             => [
						'type'    => 'array',
						'default' => [],
						'items'   => [
							'type'       => 'object',
							// In JSON Schema you can specify object properties in the properties attribute.
							'properties' => [
								'title'        => [
									'type' => 'string',
								],
								'description'  => [
									'type' => 'string',
								],
								'attachment'   => [
									'type' => 'integer',
								],
								'cta_link'     => [
									'type' => 'string',
								],
								'link_new_tab' => [
									'type' => 'string',
								],
								'cta_text'     => [
									'type' => 'string',
								],
							],
						],
					],
				],
			]
		);
	}

	/**
	 * Get all the data that will be needed to render the block correctly.
	 *
	 * @param array $attributes This is the array of fields of this block.
	 *
	 * @return array The data to be passed in the View.
	 */
	public function prepare_data( $attributes ): array {

		$attributes_temp = [
			'columns_block_style' => $attributes['columns_block_style'] ?? static::LAYOUT_NO_IMAGE,
			'columns_title'       => $attributes['columns_title'] ?? '',
			'columns_description' => $attributes['columns_description'] ?? '',
		];

		// Used to determine how many columns were set in the backend for this shortcode.
		$columns_set = 0;
		for ( $i = 1; $i <= static::MAX_COLUMNS; $i++ ) {
			$column_atts = [
				"title_$i"        => $attributes['columns'][ $i - 1 ]['title'] ?? '',
				"description_$i"  => $attributes['columns'][ $i - 1 ]['description'] ?? '',
				"attachment_$i"   => $attributes['columns'][ $i - 1 ]['attachment'] ?? '',
				"cta_text_$i"     => $attributes['columns'][ $i - 1 ]['cta_text'] ?? '',
				"link_$i"         => $attributes['columns'][ $i - 1 ]['cta_link'] ?? '',
				"link_new_tab_$i" => $attributes['columns'][ $i - 1 ]['link_new_tab'] ?? '',
			];

			$attributes_temp = array_merge( $attributes_temp, $column_atts );

			if ( ! empty( $attributes['columns'][ $i - 1 ]['title'] ) ) {
				$columns_set++;
			}
		}

		// Store the block attributes as expected by the twig template, old block style.
		$attributes_normalized                  = $attributes_temp;
		$attributes_normalized['no_of_columns'] = $columns_set;

		// Define the image size that will be used, based on layout chosen and number of columns.
		$columns_block_style = $attributes['columns_block_style'];
		if ( static::LAYOUT_NO_IMAGE !== $columns_block_style ) {

			if ( static::LAYOUT_TASKS === $columns_block_style || static::LAYOUT_IMAGES === $columns_block_style ) {
				if ( $columns_set >= 2 ) {
					$image_size = 'articles-medium-large';
				} else {
					$image_size = 'large';
				}
			} elseif ( static::LAYOUT_ICONS === $columns_block_style ) {
				$image_size = 'thumbnail';
			}
			for ( $i = 1; $i <= static::MAX_COLUMNS; $i ++ ) {
				list( $src ) = wp_get_attachment_image_src( $attributes_normalized[ "attachment_$i" ], $image_size );
				if ( $src ) {
					$attributes_normalized[ "attachment_$i" ] = $src;
				}
			}
		}

		$block_data = [
			'fields'              => $attributes_normalized,
			'available_languages' => P4GBKS_LANGUAGES,
		];
		return $block_data;
	}
}
