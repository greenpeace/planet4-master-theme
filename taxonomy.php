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
$context['wp_title']  = $context['page_type']->name;

wp_register_script( 'load_more', get_template_directory_uri() . '/assets/js/load_more.js', [ 'jquery', 'main' ], '0.0.1', true );
wp_enqueue_script( 'load_more' );

$post_args = [
	'posts_per_page' => 10,
	'post_type'      => 'post',
	'paged'          => 1,
];

$context['dummy_thumbnail'] = get_template_directory_uri() . '/images/dummy-thumbnail.png';

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-page-type.twig' ];
	$post_args['paged'] = get_query_var( 'page' );
	$pagetype_posts     = new \Timber\PostQuery( $post_args, 'P4_Post' );
	foreach ( $pagetype_posts as $pagetype_post ) {
		Timber::render( $templates, $context );
	}
} else {
	$pagetype_posts   = new \Timber\PostQuery( $post_args, 'P4_Post' );
	$context['posts'] = $pagetype_posts;
	Timber::render( $templates, $context );
}