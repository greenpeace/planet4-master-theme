<?php

/**
 * The Template for displaying all single posts
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since    Timber 0.1
 */

use P4\MasterTheme\Context;
use P4\MasterTheme\Post;
use P4\MasterTheme\Settings\CommentsGdpr;
use Timber\Timber;

// Initializing variables.
$context = Timber::get_context();
$timber_post = new Post($post->ID);
$context['post'] = $timber_post;

// Set Navigation Issues links.
$timber_post->set_issues_links();

// Get the cmb2 custom fields data
// Articles block parameters to populate the related-posts block(previously known as articles block)
// p4_take_action_page parameter to populate the take action boxout block
// Author override parameter. If this is set then the author profile section will not be displayed.
$page_meta_data = get_post_meta($timber_post->ID);
$page_meta_data = array_map(fn($v) => reset($v), $page_meta_data);
$page_terms_data = get_the_terms($timber_post, 'p4-page-type');
$page_terms_data = is_array($page_terms_data) ? reset($page_terms_data) : null;
$context['background_image'] = $page_meta_data['p4_background_image_override'] ?? '';
$take_action_page = $page_meta_data['p4_take_action_page'] ?? '';
$context['page_type'] = $page_terms_data->name ?? '';
$context['page_term_id'] = $page_terms_data->term_id ?? '';
$context['custom_body_classes'] = 'white-bg';
$context['page_type_slug'] = $page_terms_data->slug ?? '';
$context['social_accounts'] = $timber_post->get_social_accounts($context['footer_social_menu'] ?: []);
$context['page_category'] = 'Post Page';
$context['post_tags'] = implode(', ', $timber_post->tags());
$context['post_categories'] = implode(', ', $timber_post->categories());
// We need the explode because we want to remove "+00:00" at the end of the string.
$context['page_date'] = explode('+', get_the_date('c', $timber_post->ID))[0];
$context['old_posts_archive_notice'] = $timber_post->get_old_posts_archive_notice();

Context::set_og_meta_fields($context, $timber_post);
Context::set_campaign_datalayer($context, $page_meta_data);
Context::set_utm_params($context, $timber_post);
Context::set_reading_time_datalayer($context, $timber_post);

$context['filter_url'] = add_query_arg(
    [
        's' => ' ',
        'orderby' => 'relevant',
        'f[ptype][' . $context['page_type'] . ']' => $context['page_term_id'],
    ],
    get_home_url()
);

// Build the shortcode for related-posts block.
if ('yes' === $timber_post->include_articles) {
    $tag_id_array = [];
    foreach ($timber_post->tags() as $timber_post_tag) {
        $tag_id_array[] = $timber_post_tag->id;
    }
    $category_id_array = [];
    foreach ($timber_post->terms('category') as $category) {
        $category_id_array[] = $category->id;
    }

    $block_attributes = [
        'query' => [
            'perPage' => 3,
            'post_type' => 'post',
            'taxQuery' => [
                'post_tag' => $tag_id_array,
                'category' => $category_id_array,
            ],
            'exclude' => [$timber_post->ID],
        ],
        'className' => 'posts-list p4-query-loop is-custom-layout-list',
        'layout' => [
            'type' => 'default',
            'columnCount' => 3,
        ],
        'namespace' => 'planet4-blocks/posts-list',
    ];

    $timber_post->articles = '<!-- wp:p4/related-posts {"query_attributes" : '
        . wp_json_encode($block_attributes) .
    '} /-->';
}

if (! empty($take_action_page) && ! has_block('planet4-blocks/take-action-boxout')) {
    $timber_post->take_action_page = $take_action_page;

    $block_attributes = [
        'take_action_page' => $take_action_page,
    ];

    $timber_post->take_action_boxout = '<!-- wp:planet4-blocks/take-action-boxout '
    . wp_json_encode($block_attributes, JSON_UNESCAPED_SLASHES)
    . ' /-->';
}

// Build an arguments array to customize WordPress comment form.
$comments_args = [
    'comment_notes_before' => '',
    'comment_notes_after' => '',
    'comment_field' => Timber::compile('comment_form/comment_field.twig'),
    'submit_button' => Timber::compile(
        'comment_form/submit_button.twig',
        [
            'gdpr_checkbox' => CommentsGdpr::get_option(),
            'gdpr_label' => __(
                // phpcs:ignore Generic.Files.LineLength.MaxExceeded
                'I agree on providing my name, email and content so that my comment can be stored and displayed in the website.',
                'planet4-master-theme'
            ),
        ]
    ),
    'title_reply' => __('Leave your reply', 'planet4-master-theme'),
];

$context['comments_args'] = $comments_args;
$context['show_comments'] = comments_open($timber_post->ID);
$context['post_comments_count'] = get_comments(
    [
        'post_id' => $timber_post->ID,
        'status' => 'approve',
        'type' => 'comment',
        'count' => true,
    ]
);

Context::set_p4_blocks_datalayer($context, $timber_post);

if (post_password_required($timber_post->ID)) {
    // Password protected form validation.
    $context['is_password_valid'] = $timber_post->is_password_valid();

    // Hide the post title from links to the extra feeds.
    remove_action('wp_head', 'feed_links_extra', 3);

    $context['login_url'] = wp_login_url();

    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render('single-password.twig', $context);
} else {
    do_action('enqueue_google_tag_manager_script', $context);
    Timber::render(
        [ 'single-' . $timber_post->ID . '.twig', 'single-' . $timber_post->post_type . '.twig', 'single.twig' ],
        $context
    );
}
