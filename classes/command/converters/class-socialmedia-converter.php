<?php
/**
 * Shortcake Shortcode Social Media block to Gutenberg columns block conversion
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
class SocialMedia_Converter extends Shortcode_Converter {

	/**
	 * Clears some obsolete attributes from shortcake block.
	 *
	 * If embed_type is set to oembed remove facebook_page_tab attribute.
	 * If embed_type is not set and facebook_page_tab is set, remove facebook_page_tab attribute.
	 */
	protected function clear_attributes() {

		if ( isset( $this->block_attributes['embed_type'] ) &&
			'oembed' === $this->block_attributes['embed_type'] ) {
			unset( $this->block_attributes['facebook_page_tab'] );
		}

		if ( ! isset( $this->block_attributes['embed_type'] ) &&
			isset( $this->block_attributes['facebook_page_tab'] ) ) {
			unset( $this->block_attributes['facebook_page_tab'] );
		}
	}
}
