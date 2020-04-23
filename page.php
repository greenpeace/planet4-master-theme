<?php
/**
 * The template for displaying all pages.
 *
 * This is the template that displays all pages by default.
 * Please note that this is the WordPress construct of pages
 * and that other 'pages' on your WordPress site will use a
 * different template.
 *
 * To generate specific templates for your pages you can use:
 * /mytheme/views/page-mypage.twig
 * (which will still route through this PHP file)
 * OR
 * /mytheme/page-mypage.php
 * (in which case you'll want to duplicate this file and save to the above path)
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

/**
 * Category : Issue
 * Tag      : Campaign
 * Post     : Action
 */

use Timber\Timber;

$context        = Timber::get_context();
$post           = new P4_Post(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$page_meta_data = get_post_meta( $post->ID );
$page_meta_data = array_map( 'reset', $page_meta_data );

// Set Navigation Issues links.
$post->set_issues_links();

// Get Navigation Campaigns links.
$page_tags = wp_get_post_tags( $post->ID );
$tags      = [];

if ( is_array( $page_tags ) && $page_tags ) {
	foreach ( $page_tags as $page_tag ) {
		$tags[] = [
			'name' => $page_tag->name,
			'link' => get_tag_link( $page_tag ),
		];
	}
	$context['campaigns'] = $tags;
}

// Set GTM Data Layer values.
$post->set_data_layer();
$data_layer = $post->get_data_layer();

P4_Context::set_header( $context, $page_meta_data, $post->title );
P4_Context::set_background_image( $context );
P4_Context::set_og_meta_fields( $context, $post );
P4_Context::set_campaign_datalayer( $context, $page_meta_data );

$context['post']                = $post;
$context['social_accounts']     = $post->get_social_accounts( $context['footer_social_menu'] );
$context['page_category']       = $data_layer['page_category'];
$context['post_tags']           = implode( ', ', $post->tags() );
$context['custom_body_classes'] = 'brown-bg ';

if ( post_password_required( $post->ID ) ) {
	$context['login_url'] = wp_login_url();

	Timber::render( 'single-page.twig', $context );
} else {
	Timber::render( [ 'page-' . $post->post_name . '.twig', 'page.twig' ], $context );
}
