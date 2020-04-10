<?php
/**
 * P4 Context Controller Class
 *
 * @package P4MT
 */

/**
 * Class P4_Context_Controller Sets common context fields.
 */
class P4_Context_Controller {

	/**
	 * Sets most common context fields.
	 *
	 * @param object  $post That the context refers to.
	 * @param array   $context To be set.
	 * @param array   $page_meta_data  meta data of page.
	 * @param boolean $is_front_page current page is the front page or not.
	 */
	public static function set_context( $post, &$context, $page_meta_data, $is_front_page ) {
		$context['post']                        = $post;
		$context['header_title']                = $is_front_page ? ( $page_meta_data['p4_title'][0] ?? '' ) : ( $page_meta_data['p4_title'][0] ?? $post->title );
		$context['header_subtitle']             = $page_meta_data['p4_subtitle'][0] ?? '';
		$context['header_description']          = wpautop( $page_meta_data['p4_description'][0] ?? '' );
		$context['header_button_title']         = $page_meta_data['p4_button_title'][0] ?? '';
		$context['header_button_link']          = $page_meta_data['p4_button_link'][0] ?? '';
		$context['header_button_link_checkbox'] = $page_meta_data['p4_button_link_checkbox'][0] ?? '';
		$context['hide_page_title_checkbox']    = $page_meta_data['p4_hide_page_title_checkbox'][0] ?? '';
	}

	/**
	 * Set less common miscellaneous context fields.
	 *
	 * @param object $post That the context refers to.
	 * @param array  $context To be set.
	 * @param String $page_category Category of the page.
	 */
	public static function set_alternate_context( $post, &$context, $page_category ) {
		$context['social_accounts'] = $post->get_social_accounts( $context['footer_social_menu'] );
		$context['page_category']   = $page_category;
		$context['post_tags']       = implode( ', ', $post->tags() );
	}

	/**
	 * Set context fileds relating to the background image.
	 *
	 * @param array   $context To be set.
	 * @param integer $post_id Id of current post.
	 */
	public static function set_background_image_context( &$context, $post_id ) {
		$background_image_id                = get_post_meta( $post_id, 'background_image_id', 1 );
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
	public static function set_campaign_datalayer_context( &$context, $meta ) {
		$context['cf_campaign_name'] = $meta['p4_campaign_name'][0] ?? '';
		$context['cf_basket_name']   = $meta['p4_basket_name'][0] ?? '';
		$context['cf_scope']         = $meta['p4_scope'][0] ?? '';
		$context['cf_department']    = $meta['p4_department'][0] ?? '';
	}

}
