<?php
/**
 * Template Variables for Campaigns.
 *
 * @package P4MT
 */

use Timber\Timber;

// Initializing variables.
$context = Timber::get_context();

/**
 * Post object.
 *
 * @var P4_Post $post
 * */
$post            = Timber::query_post( false, 'P4_Post' ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$context['post'] = $post;

$meta = get_post_meta( $post->ID );
// No need to check the user here as that already happens in wp_get_post_autosave.
if ( isset( $_GET['preview'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	$post_preview = wp_get_post_autosave( $post->ID );
	if ( $post_preview ) {
		$meta = array_merge( $meta, get_post_meta( $post_preview->ID ) );
	}
}
$meta = array_map( 'reset', $meta );

$current_level_campaign_id = $post->ID;

do {
	$top_level_campaign_id     = $current_level_campaign_id;
	$current_level_campaign_id = wp_get_post_parent_id( $current_level_campaign_id );
} while ( $current_level_campaign_id );

if ( $top_level_campaign_id === $post->ID ) {
	$campaign_meta = $meta;
} else {
	$campaign_meta = get_post_meta( $top_level_campaign_id );
	$campaign_meta = array_map( 'reset', $campaign_meta );
}

// This is just an example of how to get children pages, this will probably be done in some kind of menu block.
$sub_pages = get_children(
	[
		'post_parent' => $post->ID,
		'post_type'   => 'campaign',
	]
);

$context['$sub_pages'] = array_map(
	static function ( $page ) {
		return [
			'link'  => get_permalink( $page->ID ),
			'title' => $page->post_title,
		];
	},
	$sub_pages
);


$theme_name = $campaign_meta['theme'] ?? $campaign_meta['_campaign_page_template'] ?? null;

if ( $theme_name ) {
	$context['custom_body_classes'] = 'white-bg theme-' . $theme_name;
}

// Save custom style settings.
$custom_styles = [];

$custom_styles['nav_type']            = $campaign_meta['campaign_nav_type'] ?? null;
$custom_styles['nav_border']          = $campaign_meta['campaign_nav_border'] ?? null;
$custom_styles['campaign_logo_color'] = 'green';
$custom_styles['campaign_logo']       = P4_Post_Campaign::get_logo( $campaign_meta );

if ( P4_Post_Campaign::DEFAULT_NAVBAR_THEME !== $custom_styles['nav_type'] ) {
	$custom_styles['campaign_logo_color'] = isset( $campaign_meta['campaign_logo_color'] ) && ! empty( $campaign_meta['campaign_logo_color'] )
		? $campaign_meta['campaign_logo_color']
		: 'light';
}

// Set GTM Data Layer values.
$post->set_data_layer();
$data_layer = $post->get_data_layer();

P4_Context_Controller::set_context( $post, $context, $meta, is_front_page() );
P4_Context_Controller::set_alternate_context( $post, $context, $data_layer['page_category'] );
P4_Context_Controller::set_background_image_context( $context, get_the_ID() );
P4_Context_Controller::set_og_meta_fields( $context, $post );
P4_Context_Controller::set_campaign_datalayer_context( $context, $campaign_meta );

$context['custom_styles'] = $custom_styles;
$context['css_vars']      = P4_Post_Campaign::css_vars( $campaign_meta );

// Social footer link overrides.
$context['social_overrides'] = [];

foreach ( range( 1, 5 ) as $i ) {
	$footer_item_key = 'campaign_footer_item' . $i;

	if ( isset( $campaign_meta[ $footer_item_key ] ) ) {
		$campaign_footer_item = maybe_unserialize( $campaign_meta[ $footer_item_key ] );
		if ( $campaign_footer_item['url'] && $campaign_footer_item['icon'] ) {
			$context['social_overrides'][ $i ]['url']  = $campaign_footer_item['url'];
			$context['social_overrides'][ $i ]['icon'] = $campaign_footer_item['icon'];
		}
	}
}

if ( post_password_required( $post->ID ) ) {
	$context['login_url'] = wp_login_url();

	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( [ 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ], $context );
}
