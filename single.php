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

use P4\MasterTheme\Context;
use P4\MasterTheme\Post;
use Timber\Timber;

// Initializing variables.
$context = Timber::get_context();
/**
 * P4 Post Object
 *
 * @var Post $post
 */
$post            = Timber::query_post( false, Post::class ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$context['post'] = $post;

// Strip Take Action Boxout block from the post content to add outside the normal block container.
if ( false !== strpos( $post->post_content, '<!-- wp:planet4-blocks/take-action-boxout' ) ) {

	$take_action_boxout_block_start  = strpos( $post->post_content, '<!-- wp:planet4-blocks/take-action-boxout' );
	$take_action_boxout_block_end    = strpos( $post->post_content, '/-->', $take_action_boxout_block_start ) + 3;
	$take_action_boxout_block_length = $take_action_boxout_block_end - $take_action_boxout_block_start + 1;
	$take_action_boxout_block        = substr( $post->post_content, $take_action_boxout_block_start, $take_action_boxout_block_length );

	$post->post_content = str_replace( $take_action_boxout_block, '', $post->post_content );
}

// Set Navigation Issues links.
$post->set_issues_links();

// Get the cmb2 custom fields data
// Articles block parameters to populate the articles block
// p4_take_action_page parameter to populate the take action boxout block
// Author override parameter. If this is set then the author profile section will not be displayed.
$page_meta_data                 = get_post_meta( $post->ID );
$page_meta_data                 = array_map( 'reset', $page_meta_data );
$page_terms_data                = get_the_terms( $post, 'p4-page-type' );
$page_terms_data                = is_array( $page_terms_data ) ? reset( $page_terms_data ) : null;
$context['background_image']    = $page_meta_data['p4_background_image_override'] ?? '';
$take_action_page               = $page_meta_data['p4_take_action_page'] ?? '';
$context['page_type']           = $page_terms_data->name ?? '';
$context['page_term_id']        = $page_terms_data->term_id ?? '';
$context['custom_body_classes'] = 'white-bg';
$context['page_type_slug']      = $page_terms_data->slug ?? '';
$context['social_accounts']     = $post->get_social_accounts( $context['footer_social_menu'] );
$context['page_category']       = 'Post Page';
$context['post_tags']           = implode( ', ', $post->tags() );

Context::set_og_meta_fields( $context, $post );
Context::set_campaign_datalayer( $context, $page_meta_data );
Context::set_utm_params( $context, $post );

$context['filter_url'] = add_query_arg(
	[
		's'                                       => ' ',
		'orderby'                                 => 'relevant',
		'f[ptype][' . $context['page_type'] . ']' => $context['page_term_id'],
	],
	get_home_url()
);

// Build the shortcode for articles block.
if ( 'yes' === $post->include_articles ) {
	$tag_id_array = [];
	foreach ( $post->tags() as $post_tag ) {
		$tag_id_array[] = $post_tag->id;
	}
	$category_id_array = [];
	foreach ( $post->terms( 'category' ) as $category ) {
		$category_id_array[] = $category->id;
	}

	$block_attributes = [
		'exclude_post_id' => $post->ID,
		'tags'            => $tag_id_array,
		'post_categories' => $category_id_array,
	];

	$post->articles = '<!-- wp:planet4-blocks/articles ' . wp_json_encode( $block_attributes, JSON_UNESCAPED_SLASHES ) . ' /-->';
}

// Build the shortcode for take action boxout block
// Break the content to retrieve first 2 paragraphs and split the content if the take action page has been defined.
if ( isset( $take_action_boxout_block ) ) {
	$post->take_action_boxout = $take_action_boxout_block;
} elseif ( ! empty( $take_action_page ) ) {
	$post->take_action_page = $take_action_page;

	$block_attributes = [
		'take_action_page' => $take_action_page,
	];

	$post->take_action_boxout = '<!-- wp:planet4-blocks/take-action-boxout ' . wp_json_encode( $block_attributes, JSON_UNESCAPED_SLASHES ) . ' /-->';
}

// Build an arguments array to customize WordPress comment form.
$comments_args = [
	'comment_notes_before' => '',
	'comment_notes_after'  => '',
	'comment_field'        => Timber::compile( 'comment_form/comment_field.twig' ),
	'submit_button'        => Timber::compile( 'comment_form/submit_button.twig' ),
	'title_reply'          => __( 'Leave your reply', 'planet4-master-theme' ),
	'fields'               => apply_filters(
		'comment_form_default_fields',
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
		'status'  => 'approve',
		'type'    => 'comment',
		'count'   => true,
	]
);

if ( post_password_required( $post->ID ) ) {

	// Password protected form validation.
	$context['is_password_valid'] = $post->is_password_valid();

	// Hide the post title from links to the extra feeds.
	remove_action( 'wp_head', 'feed_links_extra', 3 );

	$context['login_url'] = wp_login_url();

	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( [ 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ], $context );
}
