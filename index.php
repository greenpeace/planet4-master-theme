<?php
/**
 * The main template file
 * This is the most generic template file in a WordPress theme
 * and one of the two required files for a theme (the other being main.css).
 * It is used to display a page when nothing more specific matches a query.
 * E.g., it puts together the home page when no home.php file exists
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

$context                          = Timber::get_context();
$context['posts']                 = Timber::get_posts();
$context['google_tag_value']      = get_option( 'google_tag_manager_identifier', '' ) ? get_option( 'google_tag_manager_identifier' ) : '';

// Footer Items.
$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
$context['copyright_text']        = get_option( 'copyright', '' ) ? get_option( 'copyright' ) : '';

$templates = array( 'index.twig' );
if ( is_home() ) {
	array_unshift( $templates, 'home.twig' );
}
Timber::render( $templates, $context );
