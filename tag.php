<?php
/**
 * The template for displaying a Tag page.
 *
 * Learn more: https://codex.wordpress.org/Tag_Templates
 *
 * Category <-> Issue
 * Tag <-> Campaign
 * Post <-> Action
 */

use Timber\Timber;
use P4BKS\Controllers\Blocks\Covers_Controller as Covers;
use P4BKS\Controllers\Blocks\Articles_Controller as Articles;
use P4BKS\Controllers\Blocks\ContentFourColumn_Controller as ContentFourColumn;
use P4BKS\Controllers\Blocks\CampaignThumbnail_Controller as CampaignThumbnail;
use P4BKS\Controllers\Blocks\HappyPoint_Controller as HappyPoint;

$templates = array( 'tag.twig', 'archive.twig', 'index.twig' );

$context = Timber::get_context();

if ( is_tag() ) {
	$context['tag']             = get_queried_object();
	$context['post']            = Timber::get_posts()[0];       // Retrieves latest Campaign.
	$category                   = get_the_category( $context['post']->ID )[0];
	$context['category_name']   = $category->name ?? __( 'This Campaign is not assigned to an Issue', 'planet4-master-theme' );
	$context['category_link']   = get_permalink( get_page_by_title( $category->name ) );
	$context['tag_name']        = single_tag_title( '', false );
	$context['tag_description'] = $context['tag']->description;
	$context['tag_image']       = get_term_meta( $context['tag']->term_id, 'tag_attachment', true );

	// Footer Items.
	$context['footer_social_menu']    = wp_get_nav_menu_items( 'Footer Social' );
	$context['footer_primary_menu']   = wp_get_nav_menu_items( 'Footer Primary' );
	$context['footer_secondary_menu'] = wp_get_nav_menu_items( 'Footer Secondary' );
	$context['copyright_text']        = planet4_get_option( 'copyright' ) ?? '';
	$context['page_category']         = $category->name ?? __( 'Unknown Campaign page', 'planet4-master-theme' );
	$context['google_tag_value']      = planet4_get_option( 'google_tag_manager_identifier' ) ?? '';


	$campaign = new P4_Taxonomy_Campaign( $templates, $context );

	$campaign->add_block( Covers::BLOCK_NAME, [
		'title'       => __( 'Things you can do', 'planet4-master-theme' ),
		'description' => __( 'We want you to take action because together we\'re strong.', 'planet4-master-theme' ),
		'select_tag'  => $context['tag']->term_id,
	] );

	$campaign->add_block( Articles::BLOCK_NAME, [
		'article_heading' => __( 'In the news', 'planet4-master-theme' ),
		'article_count'   => 3,
	] );

	$campaign->add_block( ContentFourColumn::BLOCK_NAME, [
		'select_tag' => $context['tag']->term_id,
	] );

	$campaign->add_block( CampaignThumbnail::BLOCK_NAME, [
		'title'       => __( 'Related Campaigns', 'planet4-master-theme' ),
		'category_id' => $category->term_id ?? __( 'This Campaign is not assigned to an Issue', 'planet4-master-theme' ),
	] );

	$campaign->add_block( HappyPoint::BLOCK_NAME, [
		'background'       => get_term_meta( $context['tag']->term_id, 'happypoint_attachment_id', true ),
		'boxout_title'     => __( 'Get action alerts in your inbox', 'planet4-master-theme' ),
		'boxout_descr'     => __( 'Some text here about the transparency of the communications. Opt out or contact us at any time.', 'planet4-master-theme' ),
		'boxout_link_text' => __( 'Subscribe', 'planet4-master-theme' ),
		'boxout_link_url'  => '#',
	] );

	$campaign->view();
}
