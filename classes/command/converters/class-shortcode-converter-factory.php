<?php
/**
 * Shortcode to Gutenberg conversion command
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command\Converters;

/**
 * Class for updating old shortcodes to Gutenberg blocks
 */
class Shortcode_Converter_Factory {

	/**
	 * @param string $shortcode_name Shortcode name.
	 * @param array  $attributes     Shortcode attributes.
	 *
	 * @return Columns_Converter|Shortcode_Converter
	 */
	public static function get_converter( $shortcode_name, $attributes ) {
		switch ( $shortcode_name ) {
			case 'shortcake_enblock':
				return new ENBlock_Converter( $shortcode_name, $attributes );
		}

		return new Shortcode_Converter( $shortcode_name, $attributes );
	}
}
