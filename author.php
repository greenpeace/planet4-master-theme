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

use Timber\Timber;
use Timber\PostQuery;

$context = Timber::get_context();

$post_args = [
	'posts_per_page' => 10,
	'post_type'      => 'post',
	'paged'          => 1,
	'meta_key'       => 'p4_author_override',
	'meta_compare'   => 'NOT EXISTS',
];

if ( isset( $wp_query->query_vars['author'] ) ) {
	$author              = new P4_User( $wp_query->query_vars['author'] );
	$context['author']   = $author;
	$context['title']    = 'Author Archives: ' . $author->name();
	$post_args['author'] = $wp_query->query_vars['author'];
}

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-author.twig' ];
	$page               = get_query_var( 'page' );
	$post_args['paged'] = $page;

	$posts = new PostQuery( $post_args, 'P4_Post' );
	foreach ( $posts as $post ) {
		$context['post'] = $post;
		Timber::render( $templates, $context );
	}
} else {
	$templates        = [ 'author.twig', 'archive.twig' ];
	$context['posts'] = new PostQuery( $post_args, 'P4_Post' );

	Timber::render( $templates, $context );
}
