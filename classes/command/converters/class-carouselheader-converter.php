<?php
/**
 * Shortcake Shortcode Carousel Header block to Gutenberg carousel header block conversion.
 * Used to convert
 *
 * [shortcake_carousel_header image_1="2492" focus_image_1="center center" header_1="O que os olhos veem, a natureza sente" subheader_1="Ano: 2018 / Agência: Y&R" description_1="Formato: meia página" link_text_1="Baixe as peças aqui" link_url_1="https://drive.google.com/drive/folders/1wg6Ljzbx1WPHPpjnWAt3Xh4ub2wYVSiF?usp=sharing" image_2="2498" focus_image_2="center center" header_2="O que os olhos veem, a natureza sente" subheader_2="Ano: 2018 / Agência: Y&R" description_2="Formato: página inteira" link_text_2="Baixe aqui as peças" link_url_2="https://drive.google.com/drive/folders/16kGY9q56kCpu4WxKNo2eNb0_CDefKE8n?usp=sharing" focus_image_3="left top" focus_image_4="left top" /]
 *
 * to
 *
 * <!-- wp:planet4-blocks/carousel-header {"slides":[{"image":2492,"focal_points":{"x":0.5,"y":0.5},"header":"O que os olhos veem, a natureza sente","subheader":"Ano: 2018 / Ag\u00eancia: Y&R","description":"Formato: meia p\u00e1gina","link_text":"Baixe as pe\u00e7as aqui","link_url":"https://drive.google.com/drive/folders/1wg6Ljzbx1WPHPpjnWAt3Xh4ub2wYVSiF?usp=sharing"},{"image":2498,"focal_points":{"x":0.5,"y":0.5},"header":"O que os olhos veem, a natureza sente","subheader":"Ano: 2018 / Ag\u00eancia: Y&R","description":"Formato: p\u00e1gina inteira","link_text":"Baixe aqui as pe\u00e7as","link_url":"https://drive.google.com/drive/folders/16kGY9q56kCpu4WxKNo2eNb0_CDefKE8n?usp=sharing"}]} /-->
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command\Converters;

/**
 * Class for updating old shortcodes to Gutenberg blocks
 */
class CarouselHeader_Converter extends Shortcode_Converter {

	/**
	 * @var string
	 */
	protected $aggregated_array = 'slides';

	/**
	 * @var string
	 */
	protected $multiple_attrs_regex = '_[1-4]';

	/**
	 * @var array
	 */
	protected $mapped_attributes = [
		'focus_image' => 'focal_points',
	];

	/**
	 * Used to define custom conversion functions that are not covered by cast_attribute function.
	 *
	 * @var array
	 */
	protected $custom_conversion_callables = [
		'focal_points' => 'convert_focus_image',
	];

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
	 * If header, image or description is not defined for one of the slides, remove the slide.
	 *
	 * @param array $columns Gutenberg block's aggregated array.
	 *
	 * @return mixed
	 */
	protected function clear_aggregated_array( $columns ) {
		return array_filter(
			$columns,
			function ( $column ) {
				return property_exists( $column, 'header' ) ||
					property_exists( $column, 'image' ) ||
					property_exists( $column, 'description' );
			}
		);
	}
}
