<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
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

$context = Timber::get_context();
$post = new TimberPost();
$context['post'] = $post;

$page_meta_data = get_post_meta( $post->ID );

$context['header_title']        = null === $page_meta_data['p4_title'][0] ? $post->title : $page_meta_data['p4_title'][0];
$context['header_subtitle']     = $page_meta_data['p4_subtitle'][0];
$context['header_description']  = $page_meta_data['p4_description'][0];
$context['header_button_title'] = $page_meta_data['p4_button_title'][0];
$context['header_button_link']  = $page_meta_data['p4_button_link'][0];
$context['google_tag_value']    = get_option( 'google_tag_manager_identifier', '' ) ? get_option( 'google_tag_manager_identifier' ) : '';

// Footer Items.
$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
$context['copyright_text']        = get_option( 'copyright', '' ) ? get_option( 'copyright' ) : '';

$page_tags = wp_get_post_tags( $post->ID );
$context['page_tags'] = $page_tags;

Timber::render( array( 'page-' . $post->post_name . '.twig', 'page.twig' ), $context );
