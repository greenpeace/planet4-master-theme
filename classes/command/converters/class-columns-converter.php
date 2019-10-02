<?php
/**
 * Shortcake Shortcode Columns block to Gutenberg columns block conversion
 * Used to convert
 *
 * [shortcake_columns columns_block_style="no_image" title_1="People Power" description_1="This.." link_1="/act/" cta_text_1="Be the change" /]
 *
 * to
 *
 * <!-- wp:planet4-blocks/columns {"columns_block_style":"no_image","columns_title":"","columns_description":"","columns":[{"title":"People Power","description":"This...","cta_link":"/act/","cta_text":"Be the change","link_new_tab":false}]} /-->
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command\Converters;

/**
 * Class for updating old shortcodes to Gutenberg blocks
 */
class Columns_Converter extends Shortcode_Converter {

	/**
	 * @var string
	 */
	protected $aggregated_array = 'columns';

	/**
	 * @var string
	 */
	protected $multiple_attrs_regex = '_[1-4]';

	/**
	 * @var array
	 */
	protected $mapped_attributes = [
		'link' => 'cta_link',
	];

	/**
	 * @param string $old_name Attribute's old name.
	 *
	 * @return mixed|string|string[]|null
	 */
	public function convert_attributes( $old_name ) {
		$normalized_attribute_key = preg_replace( '/' . $this->multiple_attrs_regex . '/', '', $old_name );
		if ( array_key_exists( $normalized_attribute_key, $this->mapped_attributes ) ) {
			$normalized_attribute_key = $this->mapped_attributes[ $normalized_attribute_key ];
		}

		return $normalized_attribute_key;
	}

	/**
	 * Clears some obsolete attributes from shortcake block.
	 *
	 * If title or description is not defined for one of the columns, remove the column.
	 *
	 * @param array $columns Gutenberg block's aggregated array.
	 *
	 * @return mixed
	 */
	public function clear_aggregated_array( $columns ) {
		return array_filter(
			$columns,
			function ( $column ) {
				return property_exists( $column, 'title' ) || property_exists( $column, 'description' );
			}
		);
	}
}
