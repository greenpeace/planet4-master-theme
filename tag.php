<?php
/**
 * Displays a Campaign (Tag) page.
 *
 * Category <-> Issue
 * Tag <-> Campaign
 * Post <-> Action
 *
 * @package P4MT
 */

use Timber\Timber;
use P4BKS\Controllers\Blocks\Covers_Controller as Covers;
use P4BKS\Controllers\Blocks\Articles_Controller as Articles;
use P4BKS\Controllers\Blocks\ContentFourColumn_Controller as ContentFourColumn;
use P4BKS\Controllers\Blocks\CampaignThumbnail_Controller as CampaignThumbnail;
use P4BKS\Controllers\Blocks\HappyPoint_Controller as HappyPoint;

$context = Timber::get_context();

if ( is_tag() ) {
	$context['tag']  = get_queried_object();
	$explore_page_id = planet4_get_option( 'explore_page' );

	$redirect_id = get_term_meta( $context['tag']->term_id, 'redirect_page', true );
	if ( $redirect_id ) {

		global $wp_query;
		$redirect_page               = get_post( $redirect_id );
		$wp_query->queried_object    = $redirect_page;
		$wp_query->queried_object_id = $redirect_page->ID;
		include 'page.php';

	} else {

		$templates = [ 'tag.twig', 'archive.twig', 'index.twig' ];

		$posts = get_posts(
			[
				'posts_per_page'   => 1,
				'offset'           => 0,
				'post_parent'      => $explore_page_id,
				'post_type'        => 'page',
				'post_status'      => 'publish',
				'suppress_filters' => false,
				'tag_slug__in'     => [ $context['tag']->slug ],
			]
		);

		$context['custom_body_classes'] = 'white-bg page-issue-page';
		$context['category_name']       = $posts[0]->post_title ?? '';
		$context['category_link']       = isset( $posts[0] ) ? get_permalink( $posts[0] ) : '';
		$context['tag_name']            = single_tag_title( '', false );
		$context['tag_description']     = wpautop( $context['tag']->description );
		$context['tag_image']           = get_term_meta( $context['tag']->term_id, 'tag_attachment', true );
		$tag_image_id                   = get_term_meta( $context['tag']->term_id, 'tag_attachment_id', true );

		$context['og_description'] = $context['tag_description'];
		if ( $tag_image_id ) {
			$context['og_image_data'] = wp_get_attachment_image_src( $tag_image_id, 'full' );
		}
		$context['page_category'] = 'Tag Page';

		$campaign = new P4_Taxonomy_Campaign( $templates, $context );

		$campaign->add_block(
			Covers::BLOCK_NAME,
			[
				'title'       => __( 'Things you can do', 'planet4-master-theme' ),
				'description' => __( 'We want you to take action because together we\'re strong.', 'planet4-master-theme' ),
				'select_tag'  => $context['tag']->term_id,
				'covers_view' => '0',   // Show 6 covers in Campaign page.
			]
		);

		$campaign->add_block(
			Articles::BLOCK_NAME,
			[
				'tags' => $context['tag']->term_id,
			]
		);

		$cfc_args = [
			'select_tag' => $context['tag']->term_id,
			'posts_view' => '0',   // Show 1 row of posts.
		];

		// Get the selected page types for this campaign so that we add posts in the CFC block only for those page types.
		$selected_page_types = get_term_meta( $context['tag']->term_id, 'selected_page_types' );
		if ( isset( $selected_page_types[0] ) && $selected_page_types[0] ) {
			foreach ( $selected_page_types[0] as $selected_page_type ) {
				$cfc_args[ "p4_page_type_$selected_page_type" ] = 'true';
			}
		} else {
			// If none is selected, then display Publications by default (for backwards compatibility).
			$cfc_args['p4_page_type_publication'] = 'true';
		}

		$campaign->add_block( ContentFourColumn::BLOCK_NAME, $cfc_args );

		$campaign->add_block(
			CampaignThumbnail::BLOCK_NAME,
			[
				'title'       => __( 'Related Campaigns', 'planet4-master-theme' ),
				'category_id' => $category->term_id ?? __( 'This Campaign is not assigned to an Issue', 'planet4-master-theme' ),
			]
		);

		// Get the image selected as background for the Subscribe section (HappyPoint block) inside the current Tag.
		$background = get_term_meta( $context['tag']->term_id, 'happypoint_attachment_id', true );
		$opacity    = get_term_meta( $context['tag']->term_id, 'happypoint_bg_opacity', true );
		$options    = get_option( 'planet4_options' );

		$campaign->add_block(
			HappyPoint::BLOCK_NAME,
			[
				'background'          => $background,
				'background_html'     => wp_get_attachment_image( $background ),
				'background_src'      => wp_get_attachment_image_src( $background, 'full' ),
				'engaging_network_id' => $options['engaging_network_form_id'] ?? '',
				'opacity'             => $opacity,
				'mailing_list_iframe' => 'true',
			]
		);

		$campaign->view();
	}
}
