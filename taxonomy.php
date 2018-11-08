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

function setAuthorOverride($post) {
	$author_override       = get_post_meta( $post->ID, 'p4_author_override', true );
	$post->author          = '' === $author_override ? get_the_author_meta( 'display_name', $post->post_author ) : $author_override;
	$post->author_url      = '' === $author_override ? get_author_posts_url( $post->post_author ) : '#';
	$post->author_override = $author_override;
	return $post;
}

$context['wp_title'] = $context['page_type']->name;

wp_register_script( 'load_more', get_template_directory_uri() . '/assets/js/load_more.js', [ 'jquery', 'main' ], '0.0.1', true );
wp_enqueue_script( 'load_more' );

$post_args = [
	'posts_per_page' => 10,
	'post_type'      => 'post',
	'paged'          => 1,
];

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-page-type.twig' ];
	$post_args['paged'] = get_query_var( 'page' );
	$pagetype_posts     = new \Timber\PostQuery( $post_args );
	foreach ( $pagetype_posts as $pagetype_post ) {
		$context['post'] = setAuthorOverride($pagetype_post);
		Timber::render( $templates, $context );
	}
} else {
	$pagetype_posts = new \Timber\PostQuery( $post_args );
	foreach ( $pagetype_posts as $pagetype_post ) {
		$pagetype_post = setAuthorOverride($pagetype_post);
	}

	$context['posts'] = $pagetype_posts;
	Timber::render( $templates, $context );
}
