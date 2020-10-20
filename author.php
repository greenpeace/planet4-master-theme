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

use P4\MasterTheme\User;
use P4\MasterTheme\Post;
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
	$author              = new User( $wp_query->query_vars['author'] );
	$context['author']   = $author;
	$context['title']    = 'Author Archives: ' . $author->name();
	$post_args['author'] = $wp_query->query_vars['author'];

	$context['page_category'] = 'Author Page';

	$context['social_accounts'] = Post::filter_social_accounts( $context['footer_social_menu'] );
	$context['og_title']        = $author->name . ' - ' . get_bloginfo( 'name' );
	$context['og_description']  = get_the_author_meta( 'description', $author->ID );
	$context['og_image_data']   = [
		'url'    => get_avatar_url( $author->ID, [ 'size' => 300 ] ),
		'width'  => '300',
		'height' => '300',
	];

	$author_share_buttons              = new stdClass();
	$author_share_buttons->title       = $author->name;
	$author_share_buttons->description = get_the_author_meta( 'description', $author->ID );
	$author_share_buttons->link        = $author->link;
	$context['author_share_buttons']   = $author_share_buttons;
}

if ( get_query_var( 'page' ) ) {
	$templates          = [ 'tease-author.twig' ];
	$page_num           = get_query_var( 'page' );
	$post_args['paged'] = $page_num;

	$author_posts = new PostQuery( $post_args, Post::class );
	foreach ( $author_posts as $author_post ) {
		$context['post'] = $author_post;
		Timber::render( $templates, $context );
	}
} else {
	$templates        = [ 'author.twig', 'archive.twig' ];
	$context['posts'] = new PostQuery( $post_args, Post::class );

	Timber::render( $templates, $context );
}
