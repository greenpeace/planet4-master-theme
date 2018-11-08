<?php
/**
 * The template for displaying Author Archive pages
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

wp_register_script( 'author', get_template_directory_uri() . '/assets/js/author.js', [ 'jquery', 'main' ], '0.0.1', true );
wp_enqueue_script( 'author' );

$context          = Timber::get_context();

$post_args = [
	'posts_per_page' => 1,
	'post_type'      => 'post',
	'paged'          => 1,
];

if ( isset( $wp_query->query_vars['author'] ) ) {
	$author              = new TimberUser( $wp_query->query_vars['author'] );
	$context['author']   = $author;
	$context['title']    = 'Author Archives: ' . $author->name();
	$post_args['author'] = $wp_query->query_vars['author'];
}

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-author.twig' ];
	$page               = get_query_var( 'page' );
	$post_args['paged'] = $page;

	$posts = new Timber\PostQuery( $post_args );
	foreach ( $posts as $post ) {
		$context['post'] = $post;
		Timber::render( $templates, $context );
	}
} else {
	$templates        = [ 'author.twig', 'archive.twig' ];
	$context['posts'] = new Timber\PostQuery( $post_args );

	Timber::render( $templates, $context );
}
