<?php
/**
 * The template for displaying Taxonomy pages.
 *
 * Used to display taxonomy-type pages
 * 
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 */

$context = Timber::get_context();
$page_type = get_queried_object();
$context['page_type'] = $page_type;

$post_args = [ 
    'posts_per_page' => 1, 
    'tax_query' => [
		[
			'taxonomy' => 'p4-page-type',
			'field'    => 'slug',
			'terms'    => 'press',
        ],
    ],
    'post_type' => 'post',
    'paged' => 1
];

if ( get_query_var('page') ) {
    $templates = [ 'tease-page-type.twig' ];
    $page = get_query_var('page');
    $post_args['paged'] = $page;

    $posts = new Timber\PostQuery( $post_args );

    foreach($posts as $post) {
        $context['post'] = $post;
        Timber::render( $templates, $context );
    }
} else {
    $templates = [ 'page_type.twig' ];
    $context['posts'] = new Timber\PostQuery( $post_args );
    
    Timber::render( $templates, $context );
}
