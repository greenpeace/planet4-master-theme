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
			case 'shortcake_carousel_header':
				return new CarouselHeader_Converter( $shortcode_name, $attributes );
			case 'shortcake_columns':
				return new Columns_Converter( $shortcode_name, $attributes );
			case 'shortcake_enblock':
				return new ENBlock_Converter( $shortcode_name, $attributes );
			case 'shortcake_gallery':
				return new Gallery_Converter( $shortcode_name, $attributes );
			case 'shortcake_happy_point':
				return new HappyPoint_Converter( $shortcode_name, $attributes );
			case 'shortcake_newcovers':
				return new Covers_Converter( $shortcode_name, $attributes );
			case 'shortcake_social_media':
				return new SocialMedia_Converter( $shortcode_name, $attributes );
			case 'shortcake_submenu':
				return new Submenu_Converter( $shortcode_name, $attributes );
			case 'shortcake_take_action_boxout':
				return new TakeActionBoxout_Converter( $shortcode_name, $attributes );
		}

		return new Shortcode_Converter( $shortcode_name, $attributes );
	}
}
