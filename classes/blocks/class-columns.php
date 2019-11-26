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
									'type' => 'boolean',
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
		// Fallback to avoid notices when block doesn't have this.
		$columns_block_style = $attributes['columns_block_style'] ?? static::LAYOUT_NO_IMAGE;

		$fields = [
			'columns_block_style' => $columns_block_style,
			'columns_title'       => $attributes['columns_title'] ?? '',
			'columns_description' => $attributes['columns_description'] ?? '',
		];

		// Only show columns that have a title or a description.
		$columns = array_filter(
			$attributes['columns'],
			static function ( array $column ) {
				return ! empty( $column['title'] ) || ! empty( $column['description'] );
			}
		);

		$columns = array_slice( $columns, 0, self::MAX_COLUMNS );

		// Used to determine how many columns were set in the backend for this shortcode.
		$number_columns = count( $columns );

		// Store the block attributes as expected by the twig template, old block style.
		$fields['no_of_columns'] = $number_columns;

		// Define the image size that will be used, based on layout chosen and number of columns.
		if ( static::LAYOUT_NO_IMAGE !== $columns_block_style ) {
			$image_size = self::get_image_size( $columns_block_style, $number_columns );

			foreach ( $columns as $key => $column ) {
				if ( empty( $column['attachment'] ) ) {
					continue;
				}
				[ $img_src ] = wp_get_attachment_image_src( $column['attachment'], $image_size );

				$columns[ $key ]['attachment'] = $img_src;
			}
		}

		$fields['columns'] = $columns;

		// enqueue script that equalizes the heights of the titles of the blocks.
		if ( ! $this->is_rest_request() ) {
			wp_enqueue_script( 'column-headers', P4GBKS_PLUGIN_URL . 'public/js/columns.js', [ 'jquery' ], '0.1', true );
		}

		$block_data = [
			'fields'              => $fields,
			'available_languages' => P4GBKS_LANGUAGES,
		];

		return $block_data;
	}

	/**
	 * Which image size should be used for a combination of layout style and number of columns?
	 *
	 * @param string $columns_block_style The columns style that was picked for the block.
	 * @param int    $number_columns The total number of columns in the block.
	 * @return string The image size.
	 */
	private static function get_image_size( string $columns_block_style, int $number_columns ): string {
		if ( in_array( $columns_block_style, [ self::LAYOUT_TASKS, self::LAYOUT_IMAGES ], true ) ) {
			if ( $number_columns >= 2 ) {
				return 'articles-medium-large';
			}

			return 'large';
		}

		return 'thumbnail';
	}
}
