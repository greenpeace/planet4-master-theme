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
use P4MT\P4_Post;

$context        = Timber::get_context();
$post           = new P4_Post(); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$page_meta_data = get_post_meta( $post->ID );

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

$context['post']                        = $post;
$context['header_title']                = is_front_page() ? ( $page_meta_data['p4_title'][0] ?? '' ) : ( $page_meta_data['p4_title'][0] ?? $post->title );
$context['header_subtitle']             = $page_meta_data['p4_subtitle'][0] ?? '';
$context['header_description']          = wpautop( $page_meta_data['p4_description'][0] ?? '' );
$context['header_button_title']         = $page_meta_data['p4_button_title'][0] ?? '';
$context['header_button_link']          = $page_meta_data['p4_button_link'][0] ?? '';
$context['header_button_link_checkbox'] = $page_meta_data['p4_button_link_checkbox'][0] ?? '';
$context['hide_page_title_checkbox']    = $page_meta_data['p4_hide_page_title_checkbox'][0] ?? '';
$context['social_accounts']             = $post->get_social_accounts( $context['footer_social_menu'] );
$context['page_category']               = $data_layer['page_category'];
$context['post_tags']                   = implode( ', ', $post->tags() );

$background_image_id                = get_post_meta( get_the_ID(), 'background_image_id', 1 );
$context['background_image']        = wp_get_attachment_url( $background_image_id );
$context['background_image_srcset'] = wp_get_attachment_image_srcset( $background_image_id, 'full' );
$context['og_title']                = $post->get_og_title();
$context['og_description']          = $post->get_og_description();
$context['og_image_data']           = $post->get_og_image();
$context['custom_body_classes']     = 'brown-bg';

// P4 Campaign/dataLayer fields.
$context['cf_campaign_name'] = $page_meta_data['p4_campaign_name'][0] ?? '';
$context['cf_basket_name']   = $page_meta_data['p4_basket_name'][0] ?? '';
$context['cf_scope']         = $page_meta_data['p4_scope'][0] ?? '';
$context['cf_department']    = $page_meta_data['p4_department'][0] ?? '';

if ( post_password_required( $post->ID ) ) {
	$context['login_url'] = wp_login_url();

	Timber::render( 'single-page.twig', $context );
} else {
	Timber::render( [ 'page-' . $post->post_name . '.twig', 'page.twig' ], $context );
}
