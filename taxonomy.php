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

$templates = [ 'taxonomy.twig', 'index.twig' ];

$context             = Timber::get_context();
$context['taxonomy'] = get_queried_object();
$context['wp_title'] = $context['taxonomy']->name;

$post_args = [
	'posts_per_page' => 10,
	'post_type'      => 'post',
	'paged'          => 1,
	'p4-page-type'   => $context['taxonomy']->slug,
];

$context['page_category']   = 'Post Type Page';
$context['dummy_thumbnail'] = get_template_directory_uri() . '/images/dummy-thumbnail.png';

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-taxonomy-post.twig' ];
	$post_args['paged'] = get_query_var( 'page' );
	$pagetype_posts     = new \Timber\PostQuery( $post_args, 'P4_Post' );
	foreach ( $pagetype_posts as $pagetype_post ) {
		$context['post'] = $pagetype_post;
		Timber::render( $templates, $context );
	}
} else {
	$pagetype_posts   = new \Timber\PostQuery( $post_args, 'P4_Post' );
	$context['posts'] = $pagetype_posts;
	Timber::render( $templates, $context );
}
