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

use Timber\Timber;

/**
 * Add custom css class for body element hook.
 *
 * @param array $classes  Array of css classes passed by the hook.
 * @return array
 */
function add_body_classes_for_post( $classes ) {
	$classes[] = 'white-bg';
	return $classes;
}
add_filter( 'body_class', 'add_body_classes_for_post' );

// Initializing variables.
$context         = Timber::get_context();
/** @var P4_Post $post */
$post            = Timber::query_post( false, 'P4_Post' );
$context['post'] = $post;

// Set Navigation Issues links.
$post->set_issues_links();

// Get the cmb2 custom fields data
// Articles block parameters to populate the articles block
// p4_take_action_page parameter to populate the take action boxout block
// Author override parameter. If this is set then the author profile section will not be displayed.
$page_meta_data              = get_post_meta( $post->ID );
$page_terms_data             = get_the_terms( $post, 'p4-page-type' );
$context['author_override']  = $page_meta_data['p4_author_override'][0] ?? '';
$context['background_image'] = $page_meta_data['p4_background_image_override'][0] ?? '';
$context['post_image_id']    = $page_meta_data['p4_background_image_override_id'][0] ?? $page_meta_data['_thumbnail_id'][0];
$take_action_page            = $page_meta_data['p4_take_action_page'][0] ?? '';
$context['page_type']        = $page_terms_data[0]->name ?? '';
$context['page_term_id']     = $page_terms_data[0]->term_id ?? '';
$context['page_category']    = $category->name ?? __( 'Post page', 'planet4-master-theme' );

$context['filter_url'] = add_query_arg( [
		's'                                   => ' ',
		'orderby'                             => 'relevant',
		'f[ptype]['.$context['page_type'].']' => $context['page_term_id'],
	], get_site_url()
);


// Build the shortcode for articles block.
if ( 'yes' === $post->include_articles ) {
	$post->articles = "[shortcake_articles exclude_post_id='".$post->ID."' /]";
}

// Build the shortcode for take action boxout block
// Break the content to retrieve first 2 paragraphs and split the content if the take action page has been defined.
if ( ! empty( $take_action_page ) ) {
	$post->take_action_page   = $take_action_page;
	$post->take_action_boxout = "[shortcake_take_action_boxout take_action_page='$take_action_page' /]";
}

// Build an arguments array to customize WordPress comment form.
$comments_args = [
	'comment_notes_before' => '',
	'comment_notes_after'  => '',
	'comment_field'        => Timber::compile( 'comment_form/comment_field.twig' ),
	'submit_button'        => Timber::compile( 'comment_form/submit_button.twig' ),
	'title_reply'          => __( 'Leave Your Reply', 'planet4-master-theme' ),
	'fields'               => apply_filters( 'comment_form_default_fields',
		[
			'author' => Timber::compile( 'comment_form/author_field.twig' ),
			'email'  => Timber::compile( 'comment_form/email_field.twig' ),
		]
	),
];

$context['comments_args']       = $comments_args;
$context['show_comments']       = comments_open( $post->ID );
$context['post_comments_count'] = get_comments(
	[
		'post_id' => $post->ID,
		'status' => 'approve',
		'type' => 'comment',
		'count' => true,
	] );

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
