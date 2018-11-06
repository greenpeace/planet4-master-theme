<?php
/**
 * The template for displaying Taxonomy pages.
 *
 * Used to display taxonomy-type pages
 * 
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 */

$templates = [ 'page_type.twig', 'index.twig' ];

$context              = Timber::get_context();
$context['page_type'] = get_queried_object();
$pagetype_posts       = Timber::get_posts();

if ( $pagetype_posts ) {
	foreach ( $pagetype_posts as $pagetype_post ) {

		$author_override                = get_post_meta( $pagetype_post->ID, 'p4_author_override', true );
		$pagetype_post->author          = '' === $author_override ? get_the_author_meta( 'display_name', $pagetype_post->post_author ) : $author_override;
		$pagetype_post->author_url      = '' === $author_override ? get_author_posts_url( $pagetype_post->post_author ) : '#';
		$pagetype_post->author_override = $author_override;
	}
}

$context['posts'] = $pagetype_posts;

Timber::render( $templates, $context );
