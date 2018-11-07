<?php
/**
 * The template for displaying Taxonomy pages.
 *
 * Used to display taxonomy-type pages
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package  WordPress
 * @subpackage  Timber
 */

use Timber\Timber;

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

$context['wp_title'] = $context['page_type']->name;

wp_register_script( 'load_more', get_template_directory_uri() . '/assets/js/load_more.js', [ 'jquery' ], '0.0.1', true );
wp_enqueue_script( 'load_more' );

$post_args = [
	'posts_per_page' => 10,
	'post_type'      => 'post',
	'paged'          => 1,
];

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-page-type.twig' ];
	$post_args['paged'] = get_query_var( 'page' );
	$pagetype_posts     = new Timber\PostQuery( $post_args );
	foreach ( $pagetype_posts as $pagetype_post ) {
		$context['post'] = $pagetype_post;
		Timber::render( $templates, $context );
	}
} else {
	$context['posts'] = new Timber\PostQuery( $post_args );
	Timber::render( $templates, $context );
}
