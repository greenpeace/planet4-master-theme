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

use P4\MasterTheme\Context;
use P4\MasterTheme\Post;
use P4\MasterTheme\TaxonomyCampaign;
use Timber\Timber;

if (!is_tag()) {
    exit();
}

$tag = get_queried_object();
$redirect_id = get_term_meta($tag->term_id, 'redirect_page', true);
$featured_action = get_posts([
    'post_type' => 'p4_action',
    'tag_id' => $tag->term_id,
    'orderby' => 'date',
    'order' => 'DESC',
    'numberposts' => 1,
])[0] ?? null;
$featured_action_id = $featured_action->ID ?? null;

if ($redirect_id) {
    global $wp_query;
    $redirect_page = get_post($redirect_id);
    $wp_query->queried_object = $redirect_page;
    $wp_query->queried_object_id = $redirect_page->ID;

    // Allow modification of redirect page behavior.
    do_action('p4_action_tag_page_redirect', $redirect_page);

    include 'page.php';
    exit();
}

$post = Timber::query_post(false, Post::class); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
$context = Timber::get_context();
if ($post instanceof \WP_Post) {
    $post = new Post($post->ID);
    Context::set_og_meta_fields($context, $post);
}

$context['tag'] = $tag;
$context['tag_name'] = single_tag_title('', false);
$context['tag_description'] = wpautop($context['tag']->description);
$context['canonical_link'] = home_url($wp->request);
$context['og_type'] = 'website';
$context['featured_action'] = $featured_action;
$context['featured_action_image'] = has_post_thumbnail($featured_action_id) ?
    get_the_post_thumbnail($featured_action_id, 'medium') : null;
$context['featured_action_url'] = get_permalink($featured_action_id);

// Temporary fix with rewind, cf. https://github.com/WordPress/gutenberg/issues/53593
rewind_posts();
$context['page_category'] = 'Listing Page';
$template = file_get_contents(get_template_directory() . "/parts/query-listing-page.html");
$content = do_blocks($template);
$context['listing_page_content'] = $content;

$templates = ['tag.twig', 'archive.twig', 'index.twig'];
$campaign = new TaxonomyCampaign($templates, $context);
$campaign->view();
