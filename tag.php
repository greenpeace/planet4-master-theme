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

/**
 * TODO - Replace hard-coded block names with constants to be added in every block and which will
 * TODO - allow us to access easily the block's name ( e.g. Covers::BLOCK_NAME ),
 * TODO - depracate the protected $block_name property and deprecate the load() method from child blocks.
 */
$templates = array( 'tag.twig', 'archive.twig', 'index.twig' );

$context = Timber::get_context();

if ( is_tag() ) {
	$context['tag']             = get_queried_object();
	$context['post']            = Timber::get_posts()[0];       // Retrieves latest Campaign.
	$category                   = get_the_category( $context['post']->ID )[0];
	$context['category_name']   = $category->name ?? __( 'This Campaign is not assigned to an Issue', 'planet4-master-theme' );

	$context['tag_slug']        = $context['tag']->slug;
	$context['tag_name']        = single_tag_title( '', false );
	$context['tag_description'] = $context['tag']->description;
	$context['tag_image']       = get_term_meta( $context['tag']->term_id, 'tag_attachment', true );
}

$campaign = new P4_Taxonomy_Campaign( $templates, $context );

$campaign->add_block( 'covers', [
	'title'       => __( 'Things you can do', 'planet4-master-theme' ),
	'description' => __( 'We want you to take action because together we\'re strong.', 'planet4-master-theme' ),
	'select_tag'  => $context['tag']->term_id,
] );

$campaign->add_block( 'articles', [
	'article_heading' => __( 'In the news', 'planet4-master-theme' ),
	'article_count'   => 3,
] );

$campaign->add_block( 'content_four_column', [
	'select_tag' => $context['tag']->term_id,
] );

$campaign->add_block( 'campaign_thumbnail', [
	'title'       => __( 'Related Campaigns', 'planet4-master-theme' ),
	'category_id' => $category->term_id ?? __( 'This Campaign is not assigned to an Issue', 'planet4-master-theme' ),
] );

$campaign->add_block( 'happy_point', [
	'background'       => get_term_meta( $context['tag']->term_id, 'happypoint_attachment_id', true ),
	'boxout_title'     => __( 'Get action alerts in your inbox', 'planet4-master-theme' ),
	'boxout_descr'     => __( 'Some text here about the transparency of the communications. Opt out or contact us at any time.', 'planet4-master-theme' ),
	'boxout_link_text' => __( 'Subscribe', 'planet4-master-theme' ),
	'boxout_link_url'  => '#',
] );

$campaign->view();
