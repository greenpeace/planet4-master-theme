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

$context = Timber::get_context();

$context['page_category']    = __( '404 Page', 'planet4-master-theme' );
$context['google_tag_value'] = get_option( 'google_tag_manager_identifier', '' ) ?? '';

Timber::render( '404.twig', $context );
