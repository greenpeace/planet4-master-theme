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
$post            = Timber::query_post( false, 'P4_Post' );
$context['post'] = $post;

// Get the cmb2 custom fields data.
$page_meta_data    = get_post_meta( $post->ID );
$campaign_template = ! empty( $page_meta_data['_campaign_page_template'][0] ) ? $page_meta_data['_campaign_page_template'][0] : false;

if ( $campaign_template ) {
	$context['custom_body_classes'] = 'brown-bg theme-' . $campaign_template;
}

// Save custom style settings.
$custom_styles = [];

// Set specific CSS for Montserrat.
$pf = $post->campaign_header_primary;

$header_font_style = [
	'Montserrat'       => "font-family: 'Montserrat' !important; font-weight: 900 !important;",
	'Montserrat_Light' => "font-family: 'Montserrat' !important; font-weight: 500 !important;",
];

if ( $pf && array_key_exists( $pf, $header_font_style ) ) {
	$header_font = $header_font_style[ $pf ];
} else {
	$header_font = "font-family: {$pf} !important;";
}

$footer_links_color = 'light' === $post->campaign_logo_color ? '#FFFFFF' : '#1A1A1A';

$footer_theme = $post->campaign_footer_theme ?? null;

if ( 'white' == $footer_theme ) {
	$footer_links_color = $post->campaign_nav_color ?? '#1A1A1A';
	$footer_color       = '#FFFFFF';
} else {
	$footer_color = $post->campaign_nav_color ?? null;
}

$passive_button_colors_map = [
	'#ffd204' => '#ffe467',
	'#66cc00' => '#66cc00',
	'#6ed961' => '#a7e021',
	'#21cbca' => '#77ebe0',
	'#ee562d' => '#f36d3a',
	'#7a1805' => '#a01604',
	'#2077bf' => '#2077bf',
];

$campaigns_font_map = [
	'default'   => 'lora',
	'antarctic' => 'sanctuary',
	'arctic'    => 'Save the Arctic',
	'climate'   => 'Jost',
	'forest'    => 'Kanit',
	'oceans'    => 'Montserrat',
	'oil'       => 'Anton',
	'plastic'   => 'Montserrat',
];

if ( $campaign_template ) {
	$context['custom_body_classes'] = 'brown-bg theme-' . $campaign_template;
	$campaign_font                  = $campaigns_font_map[ $campaign_template ];
} else {
	$campaign_font = $campaigns_font_map['default'];
}

if ( 'campaign' == $post->campaign_body_font ) {
	$body_font = $campaign_font;
} else {
	$body_font = $post->campaign_body_font ?? null;
}

$custom_styles['css']['nav_color']               = $post->campaign_nav_color ? ".navbar { background-color: {$post->campaign_nav_color} !important;}" : null;
$custom_styles['nav_type']                       = $post->campaign_nav_type;
$custom_styles['nav_border']                     = $post->campaign_nav_border;
$custom_styles['campaign_logo_color']            = $post->campaign_logo_color ?? 'light';
$custom_styles['css']['footer_color']            = $footer_color ? ".site-footer { background-color: {$footer_color} !important;}" : null;
$custom_styles['css']['footer_svg_icons_color']  = ".site-footer_min .icon { fill: {$footer_links_color} !important }";
$custom_styles['css']['footer_elements_color']   = ".site-footer a { color: {$footer_links_color} !important }";
$custom_styles['css']['footer_separatos_color']  = ".site-footer li { color: {$footer_links_color} !important }";
$custom_styles['css']['footer_year_color']       = ".site-footer .gp-year { color: {$footer_links_color} !important; font-family: Roboto !important }";
$custom_styles['css']['footer_copyright_color']  = ".site-footer .copyright-text { color: {$footer_links_color} !important }";
$custom_styles['css']['header_color']            = $post->campaign_header_color ? " h1, h2, h3, h4, h5 { color: {$post->campaign_header_color} !important;}" : null;
$custom_styles['css']['campaign_header_primary'] = $post->campaign_header_primary ? " h1, h2, h3, h4, h5 { {$header_font} }" : null;
$custom_styles['css']['header_serif']            = $post->campaign_header_serif ? " .page-header { font-family: {$post->campaign_header_serif}!important;}" : null;
$custom_styles['css']['header_sans']             = $post->campaign_header_sans ? " .page-header { font-family: {$post->campaign_header_sans} !important;}" : null;
$custom_styles['css']['body_font']               = $body_font ? " body, p { font-family: '{$body_font}' !important;}" : null;
$custom_styles['css']['btn_primary']             = $post->campaign_primary_color ? " .btn-primary { background: {$passive_button_colors_map[$post->campaign_primary_color]} !important; border-color: {$passive_button_colors_map[$post->campaign_primary_color]} !important;}" : null;
$custom_styles['css']['btn_primary_hover']       = $post->campaign_primary_color ? " .btn-primary:hover { background: {$post->campaign_primary_color} !important; border-color: {$post->campaign_primary_color} !important;}" : null;
$custom_styles['css']['btn_secondary']           = $post->campaign_secondary_color
	? " .btn-secondary, .btn-action.cover-card-btn {
			background: rgba(255, 255, 255, .75) !important;
			border: 1px solid {$post->campaign_secondary_color} !important;
			color: {$post->campaign_secondary_color} !important;
		}"
	: null;
$custom_styles['css']['btn_secondary_hover']     = $post->campaign_secondary_color
	? " .btn-secondary:hover {
			background: {$post->campaign_secondary_color} !important;
			border: 1px solid {$post->campaign_secondary_color} !important;
			color: white !important;
		}"
	: null;

$custom_styles['css']['anchor']         = $post->campaign_secondary_color ? " a { color: {$post->campaign_secondary_color } !important; }" : null;
$custom_styles['css']['cover-card-btn'] = $post->campaign_primary_color ? " .cover-card:hover .cover-card-btn { background-color: {$post->campaign_primary_color} !important; border-color: {$post->campaign_primary_color} !important;}" : null;
$custom_styles['campaign_logo']         = $post->campaign_logo ?? null;

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
$context['social_accounts']             = $post->get_social_accounts( $context['footer_social_menu'] );
$context['page_category']               = $data_layer['page_category'];
$context['post_tags']                   = implode( ', ', $post->tags() );

$background_image_id                = get_post_meta( get_the_ID(), 'background_image_id', 1 );
$context['background_image']        = wp_get_attachment_url( $background_image_id );
$context['background_image_srcset'] = wp_get_attachment_image_srcset( $background_image_id, 'full' );
$context['og_title']                = $post->get_og_title();
$context['og_description']          = $post->get_og_description();
$context['og_image_data']           = $post->get_og_image();
$context['custom_styles']           = $custom_styles;

// P4 Campaign/dataLayer fields.
$context['cf_campaign_name'] = $page_meta_data['p4_campaign_name'][0] ?? '';
$context['cf_basket_name']   = $page_meta_data['p4_basket_name'][0] ?? '';
$context['cf_scope']         = $page_meta_data['p4_scope'][0] ?? '';
$context['cf_department']    = $page_meta_data['p4_department'][0] ?? '';

// Social footer link overrides.
$context['social_overrides'] = [];

for ( $i = 1; $i <= 5; $i++ ) {
	if ( isset( $page_meta_data[ 'campaign_footer_item' . $i ] ) ) {
		$campaign_footer_item = maybe_unserialize( $page_meta_data[ 'campaign_footer_item' . $i ][0] );
		if ( $campaign_footer_item['url'] && $campaign_footer_item['icon'] ) {
			$context['social_overrides'][ $i ]['url']  = $campaign_footer_item['url'];
			$context['social_overrides'][ $i ]['icon'] = $campaign_footer_item['icon'];
		}
	}
}

if ( post_password_required( $post->ID ) ) {
	Timber::render( 'single-password.twig', $context );
} else {
	Timber::render( array( 'single-' . $post->ID . '.twig', 'single-' . $post->post_type . '.twig', 'single.twig' ), $context );
}
