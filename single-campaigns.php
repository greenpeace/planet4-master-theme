<?php
/**
 * The Template for displaying all single campaign posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

use Timber\Timber;

// Initializing variables.
$context = Timber::get_context();

// @var P4_Post object $post
$post            = Timber::query_post( false, 'P4_Post' );
$context['post'] = $post;

// Set Navigation Issues links.
$post->set_issues_links();

// Get the cmb2 custom fields data
// Articles block parameters to populate the articles block
// p4_take_action_page parameter to populate the take action boxout block
// Author override parameter. If this is set then the author profile section will not be displayed.
$page_meta_data                     = get_post_meta( $post->ID );
$campaign_template                  = ! empty( $page_meta_data['_campaign_page_template'][0] ) ? $page_meta_data['_campaign_page_template'][0] : 'antarctic';
$background_image_id                = get_post_meta( get_the_ID(), 'background_image_id', 1 );
$context['background_image']        = wp_get_attachment_url( $background_image_id );
$context['background_image_srcset'] = wp_get_attachment_image_srcset( $background_image_id, 'full' );
$context['header_title']            = is_front_page() ? ( $page_meta_data['p4_title'][0] ?? '' ) : ( $page_meta_data['p4_title'][0] ?? $post->title );
$context['header_subtitle']         = $page_meta_data['p4_subtitle'][0] ?? '';
$context['header_description']      = wpautop( $page_meta_data['p4_description'][0] ?? '' );
$context['header_button_title']     = $page_meta_data['p4_button_title'][0] ?? '';
$context['header_button_link']      = $page_meta_data['p4_button_link'][0] ?? '';
$context['page_category']           = $category->name ?? __( 'Post page', 'planet4-master-theme' );
$context['social_accounts']         = $post->get_social_accounts( $context['footer_social_menu'] );
$context['og_title']                = $post->get_og_title();
$context['og_description']          = $post->get_og_description();
$context['og_image_data']           = $post->get_og_image();
$context['custom_body_classes']     = 'brown-bg theme-' . $campaign_template;

$context['post_tags'] = implode( ', ', $post->tags() );

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
