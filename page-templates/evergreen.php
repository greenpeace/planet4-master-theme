<?php

/**
 * Template Name: Evergreen Page
 * The template for displaying evergreen pages.
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

$context['post'] = $timber_post;
$context['custom_body_classes'] = 'white-bg';
$context['page_category'] = 'Evergreen Page';
$context['social_accounts'] = $timber_post->get_social_accounts($context['footer_social_menu'] ?: []);

Context::set_header($context, $page_meta_data, $timber_post->title);
Context::set_background_image($context);
Context::set_og_meta_fields($context, $timber_post);
Context::set_p4_blocks_datalayer($context, $timber_post);

if (post_password_required($timber_post->ID)) {
    $context['login_url'] = wp_login_url();

    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render('single-page.twig', $context);
} else {
    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render([ 'evergreen.twig' ], $context);
}
