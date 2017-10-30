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

$templates = array( 'tag.twig', 'archive.twig', 'index.twig' );

$context = Timber::get_context();

if ( is_tag() ) {
	$context['tag']             = get_queried_object();
	$context['post']            = Timber::get_posts()[0];
	$context['category']        = get_the_category( $context['post']->ID )[0]->name;

	$context['tag_slug']        = $context['tag']->slug;
	$context['tag_name']        = single_tag_title( '', false );
	$context['tag_description'] = $context['tag']->description;
	$context['tag_image']       = get_term_meta( $context['tag']->term_id, 'tag_attachment', true );
}

// Covers.
$block_name = 'covers';
$data = [
	'title'       => __( 'Things you can do', 'planet4-master-theme' ),
	'description' => __( 'Tart liquorice sweet roll pastry croissant toffee mufﬁn tiramisu. Marshmallow tootsie roll sugar plum powder oatcake ginger bread. Cupcake cookie cotton candy chocolate cake oat ice sweet roll pastry croissant toffee muffin tiramisu.', 'planet4-master-theme' ),
	'select_tag'  => $context['tag']->term_id,
];
$context[ $block_name ] = do_shortcode( '[shortcake_' . $block_name . ' title="' . $data['title'] . '" description="' . $data['description'] . '" select_tag="' . $data['select_tag'] . '" /]' );

/*
 $view = new View();
 $context['covers'] = $view->get_block( $block_name, $data );
*/

// Happy Point.
$block_name = 'happy_point';
$data = [
	'background'       => 247,
	'boxout_title'     => __( 'Get action alerts in your inbox', 'planet4-master-theme' ),
	'boxout_descr'     => __( 'Some text here about the transparency of the communications. Opt out or contact us at any time.', 'planet4-master-theme' ),
	'boxout_link_text' => __( 'Subscribe', 'planet4-master-theme' ),
	'boxout_link_url'  => '#',
];
$context[ $block_name ] = do_shortcode( '[shortcake_' . $block_name . ' background="' . $data['background'] . '" boxout_title="' . $data['boxout_title'] . '" boxout_descr="' . $data['boxout_descr'] . '" boxout_link_text="' . $data['boxout_link_text'] . '" boxout_link_url="' . $data['boxout_link_url'] . '" /]' );

Timber::render( $templates, $context );
