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
$post            = Timber::query_post( false, 'P4_Post' );
$context['post'] = $post;

// Save custom style settings.
$custom_styles = [];

$custom_styles['css']['nav_color']     = $post->campaign_nav_color ? ".navbar { background-color: {$post->campaign_nav_color} !important;}" : null;
$custom_styles['nav_type']             = $post->campaign_nav_type;
$custom_styles['campaign_logo_color']  = $post->campaign_logo_color ?? 'light';
$custom_styles['css']['header_bg']     = $post->campaign_header_color ? ".page-header { background: {$post->campaign_header_color} !important;}" : null;
$custom_styles['css']['header_serif']  = $post->campaign_header_serif ? ".page-header { font-family: {$post->campaign_header_serif}!important;}" : null;
$custom_styles['css']['header_sans']   = $post->campaign_header_sans ? ".page-header { font-family: {$post->campaign_header_sans} !important;}" : null;
$custom_styles['css']['body_font']     = $post->campaign_body_font ? "body { font-family: '{$post->campaign_body_font}' !important;}" : null;
$custom_styles['css']['btn_primary']   = $post->campaign_primary_color ? ".btn-primary { background: {$post->campaign_primary_color} !important; border-color: {$post->campaign_primary_color} }" : null;
$custom_styles['css']['btn_secondary'] = $post->campaign_secondary_color ? ".btn-secondary { background: {$post->campaign_secondary_color} !important; border-color: {$post->campaign_primary_color} }" : null;
$custom_styles['campaign_logo']        = $post->campaign_logo ?? null;


// Set Navigation Issues links.
$post->set_issues_links();


// Get the cmb2 custom fields data
// Articles block parameters to populate the articles block
// p4_take_action_page parameter to populate the take action boxout block
// Author override parameter. If this is set then the author profile section will not be displayed.
$page_meta_data                 = get_post_meta( $post->ID );
$campaign_template              = ! empty( $page_meta_data['_campaign_page_template'][0] ) ? $page_meta_data['_campaign_page_template'][0] : 'antarctic';
$page_terms_data                = get_the_terms( $post, 'p4-page-type' );
$context['background_image']    = $page_meta_data['p4_background_image_override'][0] ?? '';
$take_action_page               = $page_meta_data['p4_take_action_page'][0] ?? '';
$context['page_type']           = $page_terms_data[0]->name ?? '';
$context['page_term_id']        = $page_terms_data[0]->term_id ?? '';
$context['page_category']       = $category->name ?? __( 'Post page', 'planet4-master-theme' );
$context['page_type_slug']      = $page_terms_data[0]->slug ?? '';
$context['social_accounts']     = $post->get_social_accounts( $context['footer_social_menu'] );
$context['og_title']            = $post->get_og_title();
$context['og_description']      = $post->get_og_description();
$context['og_image_data']       = $post->get_og_image();
$context['custom_styles']       = $custom_styles;
$context['custom_body_classes'] = 'brown-bg theme-' . $campaign_template;

$context['post_tags'] = implode( ', ', $post->tags() );

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
