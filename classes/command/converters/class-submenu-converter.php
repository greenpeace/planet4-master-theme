<?php
/**
 * Shortcake Shortcode Submenu block to Gutenberg submenu block conversion.
 * Used to convert
 *
 * [shortcake_submenu submenu_style="1" title="On this page" heading1="2" link1="true" heading2="3" link2="true" /]
 *
 * to
 *
 * <!-- wp:planet4-blocks/submenu {"submenu_style":1,"title":"On this page","levels":[{"heading":2,"link":true},{"heading":3,"link":true}]} /-->
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command\Converters;

/**
 * Class for updating old shortcodes to Gutenberg blocks
 */
class Submenu_Converter extends Shortcode_Converter {

	/**
	 * @var null
	 */
	protected $aggregated_array = 'levels';

	/**
	 * @var string
	 */
	protected $multiple_attrs_regex = '[1-4]';

	/**
	 * @param string $old_name Shortcode's attribute key.
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
	 * If heading is not defined for one of the levels, remove the level.
	 *
	 * @param array $columns Gutenberg block's aggregated array.
	 *
	 * @return mixed
	 */
	protected function clear_aggregated_array( $columns ) {
		return array_filter(
			$columns,
			function ( $column ) {
				return property_exists( $column, 'heading' ) && $column->heading > 0;
			}
		);
	}
}
