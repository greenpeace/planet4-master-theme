<?php
/**
 * Shortcake Shortcode Gallery block to Gutenberg Gallery block conversion
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
class Gallery_Converter extends Shortcode_Converter {

	/**
	 * Used to define custom conversion functions that are not covered by cast_attribute function.
	 *
	 * @var array
	 */
	protected $custom_conversion_callables = [
		'gallery_block_focus_points' => 'convert_gallery_focus_points',
	];
}
