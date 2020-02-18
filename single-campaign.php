<?php
/**
 * Template Variables for Campaigns.
 *
 * @package P4MT
 */

use Timber\Timber;

// Initializing variables.
$context = Timber::get_context();

/**
 * Post object.
 *
 * @var P4_Post $post
 * */
$post                   = Timber::query_post( false, 'P4_Post' );
$context['post']        = $post;
$context['is_campaign'] = true;

// Get the cmb2 custom fields data.
$meta = $post->custom;
// This will later become something else than the meta of the post, but using this already so we only have to change
// this line later.
$campaign_meta = $meta;
$theme_name    = $campaign_meta['theme'] ?? $campaign_meta['_campaign_page_template'] ?? null;

if ( $theme_name ) {
	$context['custom_body_classes'] = 'brown-bg theme-' . $theme_name;
}

// Save custom style settings.
$custom_styles = [];

$custom_styles['nav_type']            = $campaign_meta['campaign_nav_type'] ?? null;
$custom_styles['nav_border']          = $campaign_meta['campaign_nav_border'] ?? null;
$custom_styles['campaign_logo_color'] = isset( $campaign_meta['campaign_logo_color'] ) && ! empty( $campaign_meta['campaign_logo_color'] ) ? $campaign_meta['campaign_logo_color'] : 'light';
$custom_styles['campaign_logo']       = P4_Post_Campaign_Page::get_logo( $campaign_meta );

// Set GTM Data Layer values.
$post->set_data_layer();
$data_layer = $post->get_data_layer();

$context['post']                        = $post;
$context['header_title']                = is_front_page() ? ( $meta['p4_title'] ?? '' ) : ( $meta['p4_title'] ?? $post->title );
$context['header_subtitle']             = $meta['p4_subtitle'] ?? '';
$context['header_description']          = wpautop( $meta['p4_description'] ?? '' );
$context['header_button_title']         = $meta['p4_button_title'] ?? '';
$context['header_button_link']          = $meta['p4_button_link'] ?? '';
$context['header_button_link_checkbox'] = $meta['p4_button_link_checkbox'] ?? '';
$context['hide_page_title_checkbox']    = $meta['p4_hide_page_title_checkbox'] ?? '';
$context['social_accounts']             = $post->get_social_accounts( $context['footer_social_menu'] );
$context['page_category']               = $data_layer['page_category'];
$context['post_tags']                   = implode( ', ', $post->tags() );

$background_image_id                = get_post_meta( get_the_ID(), 'background_image_id', 1 );
$context['background_image']        = wp_get_attachment_url( $background_image_id );
$context['background_image_srcset'] = wp_get_attachment_image_srcset( $background_image_id, 'full' );
$context['og_title']                = $post->get_og_title();
$context['og_description']          = $post->get_og_description();
$context['og_image_data']           = $post->get_og_image();
$context['custom_styles']           = $custom_styles;
$context['css_vars']                = P4_Post_Campaign_Page::css_vars( $campaign_meta );

// P4 Campaign/dataLayer fields.
$context['cf_campaign_name'] = $campaign_meta['p4_campaign_name'] ?? '';
$context['cf_basket_name']   = $campaign_meta['p4_basket_name'] ?? '';
$context['cf_scope']         = $campaign_meta['p4_scope'] ?? '';
$context['cf_department']    = $campaign_meta['p4_department'] ?? '';

// Social footer link overrides.
$context['social_overrides'] = [];

foreach ( range( 1, 5 ) as $i ) {
	$footer_item_key = 'campaign_footer_item' . $i;

	if ( isset( $campaign_meta[ $footer_item_key ] ) ) {
		$campaign_footer_item = maybe_unserialize( $campaign_meta[ $footer_item_key ] );
		if ( $campaign_footer_item['url'] && $campaign_footer_item['icon'] ) {
			$context['social_overrides'][ $i ]['url']  = $campaign_footer_item['url'];
			$context['social_overrides'][ $i ]['icon'] = $campaign_footer_item['icon'];
		}
	}
}

if ( post_password_required( $post->ID ) ) {
	$context['login_url'] = wp_login_url();

	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
