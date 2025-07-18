<?php

/**
 * The Template for displaying all action pages
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * (Note: This file is a copy of page.php to use the same template for action pages.)
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

if (!$post) {
    return;
}

use P4\MasterTheme\Context;
use P4\MasterTheme\Post;
use Timber\Timber;

$context = Timber::get_context();
$timber_post = new Post($post->ID);
$page_meta_data = get_post_meta($timber_post->ID);
$page_meta_data = array_map(fn ($v) => reset($v), $page_meta_data);

// Set GTM Data Layer values.
$timber_post->set_data_layer();
$data_layer = $timber_post->get_data_layer();

Context::set_header($context, $page_meta_data, $timber_post->title);
Context::set_background_image($context);
Context::set_og_meta_fields($context, $timber_post);
Context::set_campaign_datalayer($context, $page_meta_data);
Context::set_utm_params($context, $timber_post);
Context::set_custom_styles($context, $page_meta_data);

$context['post'] = $timber_post;
$context['social_accounts'] = $timber_post->get_social_accounts($context['footer_social_menu'] ?: []);
$context['page_category'] = 'Actions';
$context['post_tags'] = implode(', ', $timber_post->tags());
$context['post_categories'] = implode(', ', $timber_post->categories());
$context['custom_body_classes'] = 'brown-bg ';

Context::set_p4_blocks_datalayer($context, $timber_post);

if (post_password_required($timber_post->ID)) {
    // Password protected form validation.
    $context['is_password_valid'] = $timber_post->is_password_valid();

    // Hide the page title from links to the extra feeds.
    remove_action('wp_head', 'feed_links_extra', 3);

    $context['login_url'] = wp_login_url();

    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render('single-page.twig', $context);
} else {
    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render([ 'page-' . $timber_post->post_name . '.twig', 'page.twig' ], $context);
}
