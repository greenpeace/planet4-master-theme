<?php
/**
 * The template for displaying 404 pages (Not Found)
 *
 * Methods for TimberHelper can be found in the /functions sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

/**
 * Add custom css class for body element hook.
 *
 * @param array $classes Array of css classes passed by the hook.
 *
 * @return array
 */
function add_body_classes( $classes ) {
	$classes[] = 'brown-bg page-404-page';

	return $classes;
}
add_filter( 'body_class', 'add_body_classes' );

$context = Timber::get_context();

$options                        = get_option( 'planet4_options' );
$context['page_notfound_image'] = $options['404_page_bg_image'] ?? esc_url( get_template_directory_uri() . '/images/404-header.jpg' );
$context['page_notfound_text']  = $options['404_page_text'] ?? __( 'Sorry, we can\'t find that page!', 'planet4-master-theme' );
$context['page_notfound_help']  = __( 'Enter your search term below', 'planet4-master-theme' );
$context['page_category']       = __( '404 Page', 'planet4-master-theme' );

Timber::render( '404.twig', $context );
