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
class Shortcode_Converter {

	use Conversion_Functions;

	/**
	 * @var string
	 */
	protected $shortcode_name;

	/**
	 * @var array
	 */
	protected $shortcode_attributes;

	/**
	 * @var array
	 */
	protected $block_attributes;

	/**
	 * @var string
	 */
	protected $block_name;

	/**
	 * @var string
	 */
	protected $block_type;

	/**
	 * Defines custom conversion functions for some shortcodes attributes that need extra logic.
	 *
	 * @var array
	 */
	protected $custom_conversion_callables = [];

	/**
	 * Defines custom conversion functions for some shortcodes attributes that need extra logic.
	 *
	 * @var array
	 */
	protected $mapped_attributes = [];

	/**
	 * @var null
	 */
	protected $aggregated_array = null;

	/**
	 * Constructor
	 * Initialize properties
	 *
	 * @param string $shortcode_name Shortcode name.
	 * @param array  $attributes Shortcode attributes.
	 */
	public function __construct( $shortcode_name, $attributes ) {
		$block_types = \WP_Block_Type_Registry::get_instance()->get_all_registered();

		$block_name                 = str_replace( 'shortcake_', '', $shortcode_name );
		$block_name                 = str_replace( '_', '-', $block_name );
		$block_name                 = 'planet4-blocks/' . $block_name;
		$this->block_name           = $block_name;
		$this->shortcode_name       = $shortcode_name;
		$this->shortcode_attributes = $attributes;
		if ( array_key_exists( $block_name, $block_types ) ) {
			$this->block_type = $block_types[ $block_name ];
		}
	}

	/**
	 * Converts the shortcode to the gutenberg block equivalent.
	 *
	 * @throws \Exception - if no posts found.
	 */
	public function convert() {
		if ( ! $this->block_type ) {
			return '';
		}

		// Temp array for converted attributes.
		$converted_attributes = [];
		$temp_arr             = [];

		// Iterate over shortcode attributes.
		foreach ( $this->shortcode_attributes as $attribute_key => $attribute_value ) {

			// If attributes ends with _1 or _2 .. treat it as an attribute of shortcode's attribute
			// which is an array of objects.
			if ( isset( $this->multiple_attrs_regex ) && preg_match( '/.*' . $this->multiple_attrs_regex . '/i', $attribute_key ) ) {

				$index = intval( substr( $attribute_key, - 1 ) ) - 1;
				if ( ! isset( $temp_arr[ $index ] ) ) {
					$sub_attribute_obj  = new \stdClass();
					$temp_arr[ $index ] = $sub_attribute_obj;
				} else {
					$sub_attribute_obj = $temp_arr[ $index ];
				}

				$normalized_attribute_key                     = $this->convert_attributes( $attribute_key );
				$sub_attribute_obj->$normalized_attribute_key = $this->convert_attribute(
					$normalized_attribute_key,
					$attribute_value,
					$this->block_type->attributes[ $this->aggregated_array ]['items']['properties'][ $normalized_attribute_key ]['type']
				);
			} else {
				// Check if the shortcode attribute name has been changed in the gutenberg block and find the
				// equivalent attribute in the gutenberg block.
				if ( array_key_exists( $attribute_key, $this->mapped_attributes ) ) {
					$attribute_key = $this->mapped_attributes[ $attribute_key ];
				}
				// Check if attribute name exists in the block's attributes definitions/schema.
				if ( array_key_exists( $attribute_key, $this->block_type->attributes ) ) {
					$converted_attributes[ $attribute_key ] = $this->convert_attribute(
						$attribute_key,
						$attribute_value,
						$this->block_type->attributes[ $attribute_key ]['type']
					);
				}
			}
		}

		// Check if the block has an aggregated array attribute.
		// For shortcodes that have multiple attributes for the similar attributes.
		// Examples are columns shortcode which can have multiple titles (each for a column, eg. title_1, title_2 ...).
		if ( isset( $this->aggregated_array ) ) {
			$converted_attributes[ $this->aggregated_array ] = $this->clear_aggregated_array( $temp_arr );
		}

		$this->block_attributes = $converted_attributes;
		$this->clear_attributes();

		// Build the html comment that represents the gutenberg block.
		return $this->make_gutenberg_comment();
	}

	/**
	 * @param string $attribute_key    Shortcode's attribute key.
	 * @param string $attribute_value  Shortcode's attribute value.
	 * @param string $attribute_type   Shortcode's attribute type.
	 *
	 * @return array|int|mixed
	 */
	protected function convert_attribute( $attribute_key, $attribute_value, $attribute_type ) {
		// Check if there is a custom conversion function defined for the current shortcode attribute.
		if ( array_key_exists( $attribute_key, $this->custom_conversion_callables ) ) {
			return call_user_func(
				[
					$this,
					$this->custom_conversion_callables[ $attribute_key ],
				],
				$attribute_value
			);
		} else { // If there is no custom conversion function call generic cast_attribute function.
			return $this->cast_attribute(
				$attribute_key,
				$attribute_value,
				$attribute_type
			);
		}
	}

	/**
	 * @param string $key Shortcode's attribute key.
	 * @param string $attribute Shortcode's attribute value.
	 *
	 * @return array
	 */
	public function convert_delimited_strings_to_array( $key, $attribute ) {
		$known_arrays = [
			'tags',
			'tag_ids',
			'post_types',
			'posts',
		];

		if ( in_array( $key, $known_arrays, true ) ) {
			$attribute = array_map( 'intval', explode( ',', $attribute ) );

			$attribute = array_filter(
				$attribute,
				function ( $element ) {
					return ( $element > 0 );
				}
			);
		}

		return $attribute;
	}

	/**
	 * @param string $attribute_key Shortcode's attribute key.
	 * @param string $attribute_value Shortcode's attribute value.
	 * @param string $attribute_type Shortcode's attribute type.
	 *
	 * @return array|int|mixed
	 */
	public function cast_attribute( $attribute_key, $attribute_value, $attribute_type ) {

		switch ( $attribute_type ) {
			case 'integer':
				$attribute_value = intval( $attribute_value );
				break;
			case 'boolean':
				$attribute_value = filter_var( $attribute_value, FILTER_VALIDATE_BOOLEAN );
				break;
			case 'array':
				$attribute_value = $this->convert_delimited_strings_to_array( $attribute_key, $attribute_value );
				break;
		}

		return $attribute_value;
	}

	/**
	 * Uses block's filtered/converted attributes and it's name to convert it to a gutenberg equivalent block.
	 *
	 * @return string
	 */
	protected function make_gutenberg_comment() {
		return '<!-- wp:' . $this->block_name . ' ' . json_encode( $this->block_attributes, JSON_UNESCAPED_SLASHES ) . ' /-->'; // phpcs:ignore
	}

	/**
	 * Subclasses could override this to clear the block's attributes.
	 */
	protected function clear_attributes() {}

	/**
	 * Clear gutenberg block aggregated array based on block's logic.
	 *
	 * @param array $columns Gutenberg block's aggregated array.
	 *
	 * @return mixed
	 */
	protected function clear_aggregated_array( $columns ) {
		return $columns;
	}
}
