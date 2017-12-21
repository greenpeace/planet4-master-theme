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

$context['page_notfound_image']       = esc_url( get_template_directory_uri() . '/images/404-header.jpg' );
$context['page_notfound_title']       = __( 'Oh dear...', 'planet4-master-theme' );
$context['page_notfound_description'] = __( 'We\'re sorry it looks like the page your looking for isn\'t there. View our 404 check list and either try again or enter the pages key word our search tool.', 'planet4-master-theme' );
$context['page_notfound_checklist']   = __( 'Check list', 'planet4-master-theme' );
$context['page_notfound_help']        = __( 'Let us help you find...', 'planet4-master-theme' );

$context['checklist'][] = __( 'one', 'planet4-master-theme' );
$context['checklist'][] = __( 'two', 'planet4-master-theme' );
$context['checklist'][] = __( 'three', 'planet4-master-theme' );

$context['page_category']    = __( '404 Page', 'planet4-master-theme' );
$context['google_tag_value'] = planet4_get_option( 'google_tag_manager_identifier' ) ?? '';

Timber::render( '404.twig', $context );
