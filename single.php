<?php
/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */


$context = Timber::get_context();
$post    = Timber::query_post();

$page_meta_data = get_post_meta( $post->ID );
$articles_title = $page_meta_data['p4_articles_title'][0];
$articles_count = $page_meta_data['p4_articles_count'][0];

if ( ! empty( $articles_title ) and $articles_count and is_int( intval( $articles_count ) ) ) {
	$post->articles = do_shortcode( "[shortcake_articles article_heading='$articles_title' article_count='$articles_count' /]" );
}
$context['post'] = $post;

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
