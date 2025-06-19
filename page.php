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

use P4\MasterTheme\Context;
use P4\MasterTheme\Features\RedirectRedirectPages;
use P4\MasterTheme\Post;
use Timber\Timber;

$context = Timber::get_context();
$timber_post = new Post($post->ID);
$page_meta_data = get_post_meta($timber_post->ID) ?: [];
$page_meta_data = array_map(fn($v) => reset($v), $page_meta_data);

// Ensure redirect is only performed if we're not already on a tag URL. Because tag.php includes this file.
if (! is_tag() && RedirectRedirectPages::is_active()) {
    $args = [
        // Ensure the term is returned even if no posts are tagged with it.
        'hide_empty' => false,
        'meta_query' => [
            [
                'key' => 'redirect_page',
                'value' => $timber_post->ID,
                'compare' => '=',
            ],
        ],
        'fields' => 'ids',
    ];
    $terms = get_terms($args);

    if (! empty($terms)) {
        $term_link = get_term_link($terms[0]);

        wp_safe_redirect($term_link, 301);
        exit();
    }
}

// Set Navigation Issues links.
$timber_post->set_issues_links();

// Get Navigation Campaigns links.
$page_tags = wp_get_post_tags($timber_post->ID);
$tags = [];

if (is_array($page_tags) && $page_tags) {
    foreach ($page_tags as $page_tag) {
        $tags[] = [
            'name' => $page_tag->name,
            'link' => get_tag_link($page_tag),
        ];
    }
    $context['campaigns'] = $tags;
}

// Set GTM Data Layer values.
$timber_post->set_data_layer();
$data_layer = $timber_post->get_data_layer();

Context::set_header($context, $page_meta_data, $timber_post->title);
Context::set_background_image($context);
Context::set_og_meta_fields($context, $timber_post);
Context::set_campaign_datalayer($context, $page_meta_data);
Context::set_utm_params($context, $timber_post);
Context::set_p4_blocks_datalayer($context, $timber_post);

$context['post'] = $timber_post;
$context['social_accounts'] = $timber_post->get_social_accounts($context['footer_social_menu'] ?: []);
$context['page_category'] = $data_layer['page_category'];
$context['post_tags'] = implode(', ', $timber_post->tags());
$context['post_categories'] = implode(', ', $timber_post->categories());
$context['custom_body_classes'] = 'brown-bg ';

if (is_tag()) {
    $context['canonical_link'] = home_url($wp->request);
}

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
