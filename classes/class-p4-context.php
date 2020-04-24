<?php
/**
 * P4 Context Class
 *
 * @package P4MT
 */

/**
 * Class P4_Context Sets common context fields.
 */
class P4_Context {

	/**
	 * Set context relating to the header
	 *
	 * @param array  $context To be set.
	 * @param array  $page_meta_data  meta data of page.
	 * @param String $post_title the title of the post.
	 */
	public static function set_header( &$context, $page_meta_data, $post_title ) {
		$context['header_title']                = is_front_page() ? ( $page_meta_data['p4_title'] ?? '' ) : ( $page_meta_data['p4_title'] ?? $post_title );
		$context['header_subtitle']             = $page_meta_data['p4_subtitle'] ?? '';
		$context['header_description']          = wpautop( $page_meta_data['p4_description'] ?? '' );
		$context['header_button_title']         = $page_meta_data['p4_button_title'] ?? '';
		$context['header_button_link']          = $page_meta_data['p4_button_link'] ?? '';
		$context['header_button_link_checkbox'] = $page_meta_data['p4_button_link_checkbox'] ?? '';
		$context['hide_page_title_checkbox']    = $page_meta_data['p4_hide_page_title_checkbox'] ?? '';
	}

	/**
	 * Set context fileds relating to the background image.
	 *
	 * @param array $context To be set.
	 */
	public static function set_background_image( &$context ) {
		$background_image_id                = get_post_meta( get_the_ID(), 'background_image_id', 1 );
		$context['background_image']        = wp_get_attachment_url( $background_image_id );
		$context['background_image_srcset'] = wp_get_attachment_image_srcset( $background_image_id, 'full' );
	}

	/**
	 * Set open graph context fields.
	 *
	 * @param array  $context To be set.
	 * @param object $post That the context refers to.
	 */
	public static function set_og_meta_fields( &$context, $post ) {
		$context['og_title']       = $post->get_og_title();
		$context['og_description'] = $post->get_og_description();
		$context['og_image_data']  = $post->get_og_image();
	}

	/**
	 * Set the context fields relating to the data layer.
	 *
	 * @param array $context Context to be set.
	 * @param array $meta Meta data of the page.
	 */
	public static function set_campaign_datalayer( &$context, $meta ) {
		$context['cf_campaign_name'] = $meta['p4_campaign_name'] ?? '';
		$context['cf_basket_name']   = $meta['p4_basket_name'] ?? '';
		$context['cf_department']    = $meta['p4_department'] ?? '';
		$context['cf_project_id']    = $meta['p4_global_project_tracking_id'] ?? 'not set';
		$context['cf_local_project'] = $meta['p4_local_project'] ?? 'not set';
		$context['cf_scope']         = self::get_campaign_scope( $context['cf_campaign_name'] );
	}

	/**
	 * Get campaign scope from value selected in the Global Projects dropdown.
	 * Conditions:
	 * - If Global Project equals "Local Campaign" then Scope is Local.
	 * - If Global Project equals none then Scope is not set
	 * - If Global Project matches any other value (apart from "Local Campaign") then Scope is Global
	 *
	 * @param string $global_project The Global Project value.
	 * @return string The campaign scope.
	 */
	private static function get_campaign_scope( $global_project ) {
		switch ( $global_project ) {
			case 'Local Campaign':
				return 'Local';
			case 'not set':
				return 'not set';
			default:
				return 'Global';
		}
	}
}
