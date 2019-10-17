<?php
/**
 * Shortcake Shortcode Carousel Header block to Gutenberg carousel header block conversion
 * Used to convert
 *
 * [shortcake_social_media facebook_page_tab="timeline" social_media_url="https://www.facebook.com/greenpeacenederland/videos/419976328769397/" /]
 *
 * to
 *
 * <!-- wp:planet4-blocks/social-media {"social_media_url":"https://www.facebook.com/greenpeacenederland/videos/419976328769397/"} /-->
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command\Converters;

/**
 * Class for updating old shortcodes to Gutenberg blocks
 */
class HappyPoint_Converter extends Shortcode_Converter {

	/**
	 * @var array
	 */
	protected $mapped_attributes = [
		'background' => 'id',
	];

	/**
	 * Used to define custom conversion functions that are not covered by cast_attribute function.
	 *
	 * @var array
	 */
	protected $custom_conversion_callables = [
		'focus_image' => 'convert_focus_image_percentages',
	];

	/**
	 * Constructor
	 * Initialize properties
	 *
	 * @param string $shortcode_name Shortcode name.
	 * @param array  $attributes Shortcode attributes.
	 */
	public function __construct( $shortcode_name, $attributes ) {
		parent::__construct( $shortcode_name, $attributes );

		$this->block_name = 'planet4-blocks/happypoint';
		$block_types      = \WP_Block_Type_Registry::get_instance()->get_all_registered();
		if ( array_key_exists( $this->block_name, $block_types ) ) {
			$this->block_type = $block_types[ $this->block_name ];
		}
	}
}
