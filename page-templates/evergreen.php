<?php /* Template Name: Evergreen Page */
/**
 * The template for displaying evergreen pages.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/views/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

use Timber\Timber;

/**
 * Add custom css class for body element hook.
 *
 * @param array $classes Array of css classes passed by the hook.
 *
 * @return array
 */
function add_body_classes_for_evergreen_page( $classes ) {
	$classes[] = 'white-bg';

	return $classes;
}

add_filter( 'body_class', 'add_body_classes_for_evergreen_page' );

$context                        = Timber::get_context();
$post                           = new TimberPost();
$page_meta_data                 = get_post_meta( $post->ID );
$context['page']                = $post;
$context['header_title']        = is_front_page() ? '' : ( $page_meta_data['p4_title'][0] ?? $post->title );
$context['header_subtitle']     = $page_meta_data['p4_subtitle'][0] ?? '';
$context['header_description']  = $page_meta_data['p4_description'][0] ?? '';
$context['header_button_title'] = $page_meta_data['p4_button_title'][0] ?? '';
$context['header_button_link']  = $page_meta_data['p4_button_link'][0] ?? '';
$context['google_tag_value']    = planet4_get_option( 'google_tag_manager_identifier', '' ) ?? '';

// Footer Items.
$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
$context['copyright_text']        = planet4_get_option( 'copyright', '' ) ?? '';

$context['background_image'] = wp_get_attachment_url( get_post_meta( get_the_ID(), 'background_image_id', 1 ), 'medium' );

Timber::render( array( 'evergreen.twig' ), $context );
