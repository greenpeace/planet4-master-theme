<?php

/**
 * Template Variables for Campaigns.
 *
 * @package P4MT
 */

if (!$post) {
    return;
}

use P4\MasterTheme\Context;
use P4\MasterTheme\Post;
use Timber\Timber;

// Initializing variables.
$context = Timber::get_context();
$timber_post = new Post($post->ID);
$context['post'] = $timber_post;

// Get the cmb2 custom fields data.
$meta = $timber_post->custom;

$current_level_campaign_id = $timber_post->ID;

do {
    $top_level_campaign_id = $current_level_campaign_id;
    $current_level_campaign_id = wp_get_post_parent_id($current_level_campaign_id);
} while ($current_level_campaign_id);

if ($top_level_campaign_id === $timber_post->ID) {
    $campaign_meta = $meta;
} else {
    $parent_meta = get_post_meta($top_level_campaign_id);
    // Ensure each meta value is an array before applying reset().
    $campaign_meta = array_map(function ($meta_value) {
        return is_array($meta_value) ? reset($meta_value) : $meta_value;
    }, $parent_meta);
}

// This is just an example of how to get children pages, this will probably be done in some kind of menu block.
$sub_pages = get_children(
    [
        'post_parent' => $timber_post->ID,
        'post_type' => 'campaign',
    ]
);

$context['$sub_pages'] = array_map(
    static function ($page) {
        return [
            'link' => get_permalink($page->ID),
            'title' => $page->post_title,
        ];
    },
    $sub_pages
);

$theme_name = $campaign_meta['theme'] ?? $campaign_meta['_campaign_page_template'] ?? null;

if ($theme_name) {
    $context['custom_body_classes'] = 'white-bg theme-' . $theme_name;
}

// Set GTM Data Layer values.
$timber_post->set_data_layer();
$data_layer = $timber_post->get_data_layer();

Context::set_header($context, $meta, $timber_post->title);
Context::set_background_image($context);
Context::set_og_meta_fields($context, $timber_post);
Context::set_campaign_datalayer($context, $campaign_meta);
Context::set_utm_params($context, $timber_post);
Context::set_custom_styles($context, $campaign_meta, 'campaign');

$context['post'] = $timber_post;
$context['social_accounts'] = $timber_post->get_social_accounts($context['footer_social_menu'] ?: []);
$context['page_category'] = $data_layer['page_category'];

$context['custom_font_families'] = [
    'Montserrat:300,400,500,600,700,800',
    'Kanit:400,500,600,800',
    'Open+Sans:400,500,600,800',
    'Anton:400,500,600,800',
    'Amiko:400,500,600,800',
];

// Social footer link overrides.
$context['social_overrides'] = [];

foreach (range(1, 5) as $i) {
    $footer_item_key = 'campaign_footer_item' . $i;

    if (!isset($campaign_meta[$footer_item_key])) {
        continue;
    }

    $campaign_footer_item = maybe_unserialize($campaign_meta[$footer_item_key]);
    if (!$campaign_footer_item['url'] || !$campaign_footer_item['icon']) {
        continue;
    }

    $context['social_overrides'][$i]['url'] = $campaign_footer_item['url'];
    $context['social_overrides'][$i]['icon'] = $campaign_footer_item['icon'];
}

Context::set_p4_blocks_datalayer($context, $timber_post);

if (post_password_required($timber_post->ID)) {
    // Password protected form validation.
    $context['is_password_valid'] = $timber_post->is_password_valid();

    // Hide the campaign title from links to the extra feeds.
    remove_action('wp_head', 'feed_links_extra', 3);

    $context['login_url'] = wp_login_url();

    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render('single-password.twig', $context);
} else {
    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render(
        ['single-' . $timber_post->ID . '.twig', 'single-' . $timber_post->post_type . '.twig', 'single.twig'],
        $context
    );
}
