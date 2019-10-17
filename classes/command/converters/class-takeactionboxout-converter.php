<?php
/**
 * Shortcake Shortcode Take action boxout block to Gutenberg take action blockout block conversion.
 * Used to convert
 *
 * [shortcake_take_action_boxout take_action_page="32" /]
 *
 * to
 *
 * <!-- wp:planet4-blocks/take-action-boxout {"take_action_page":32} /-->
 *
 * @package P4GBKS
 */

namespace P4GBKS\Command\Converters;

/**
 * Class for updating old shortcodes to Gutenberg blocks
 */
class TakeActionBoxout_Converter extends Shortcode_Converter {

	/**
	 * Clears some obsolete attributes from shortcake block.
	 *
	 * If take_action_page is set clear custom attributes.
	 */
	protected function clear_attributes() {

		if ( isset( $this->block_attributes['take_action_page'] ) &&
			0 < $this->block_attributes['take_action_page'] ) {
			unset( $this->block_attributes['custom_title'] );
			unset( $this->block_attributes['custom_excerpt'] );
			unset( $this->block_attributes['custom_link_new_tab'] );
			unset( $this->block_attributes['custom_link_text'] );
		}
	}
}
