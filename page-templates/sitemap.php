<?php /* Template Name: Sitemap Page */
/**
 * The template for displaying the Sitemap page.
 */

use Timber\Timber;

/**
 * Add custom css class for body element hook.
 *
 * @param array $classes Array of css classes passed by the hook.
 *
 * @return array
 */
function add_body_classes_for_sitemap_page( $classes ) {
	$classes[] = 'white-bg';
	return $classes;
}

add_filter( 'body_class', 'add_body_classes_for_sitemap_page' );

$context        = Timber::get_context();
$post           = new P4_Post();
$sitemap        = new P4_Sitemap();
$page_meta_data = get_post_meta( $post->ID );

$context['post']             = $post;
$context['header_title']     = is_front_page() ? '' : ( $page_meta_data['p4_title'][0] ?? $post->title );
$context['background_image'] = wp_get_attachment_url( get_post_meta( get_the_ID(), 'background_image_id', 1 ) );

$context['actions_title']    = __( 'Act', 'planet4-master-theme' );
$context['issues_title']     = __( 'Explore', 'planet4-master-theme' );
$context['evergreen_title']  = __( 'About Greenpeace', 'planet4-master-theme' );
$context['page_types_title'] = __( 'Articles', 'planet4-master-theme' );
$context['archive_title']    = __( 'Search the archive', 'planet4-master-theme' );

$context['actions']          = $sitemap->get_actions();
$context['issues']           = $sitemap->get_issues();
$context['evergreen_pages']  = $sitemap->get_evergreen_pages();
$context['page_types']       = $sitemap->get_page_types();

Timber::render( array( 'sitemap.twig' ), $context );
