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
use P4\MasterTheme\Features\Dev\ListingPageGridView;
use P4GBKS\Blocks\Articles;
use P4GBKS\Blocks\HappyPoint;
use P4\MasterTheme\Post;
use P4\MasterTheme\TaxonomyCampaign;
use Timber\Timber;

if (!is_tag()) {
    exit();
}

$tag = get_queried_object();
$redirect_id = get_term_meta($tag->term_id, 'redirect_page', true);

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

Context::set_og_meta_fields($context, $post);
$context['tag'] = $tag;
$context['tag_name'] = single_tag_title('', false);
$context['tag_description'] = wpautop($context['tag']->description);
$context['canonical_link'] = home_url($wp->request);

if (!empty(planet4_get_option('new_ia'))) {
    $context['page_category'] = 'Listing Page';
    $view = ListingPageGridView::is_active() ? 'grid' : 'list';
    $query_template = file_get_contents(get_template_directory() . "/parts/query-$view.html");
    $content = do_blocks($query_template);
    $context['query_loop'] = $content;

    $templates = ['tag.twig', 'archive.twig', 'index.twig'];
    $campaign = new TaxonomyCampaign($templates, $context);
    $campaign->view();
} else {
    $context['page_category'] = 'Tag Page';
    $context['custom_body_classes'] = 'white-bg page-issue-page';
    $context['category_name'] = '';
    $context['category_link'] = '';

    $templates = ['tag.twig', 'archive.twig', 'index.twig'];
    $campaign = new TaxonomyCampaign($templates, $context);

    $campaign->add_block(
        'covers',
        [
            'title' => __('Things you can do', 'planet4-master-theme'),
            'description' => __(
                'We want you to take action because together we\'re strong.',
                'planet4-master-theme'
            ),
            'tags' => [$tag->term_id],
            'cover_type' => '1', // Show Take Action Cover.
        ]
    );

    $campaign->add_block(
        Articles::BLOCK_NAME,
        [
            'tags' => [$tag->term_id],
        ]
    );

    // Convert old CFC block to Covers block [Content Cover].
    $cfc_args = [
        'tags' => [$tag->term_id],
        'cover_type' => '3',
        'title' => __('Publications', 'planet4-master-theme'),
    ];

    // Get the selected page types for this campaign so that we add posts in the CFC block
    // only for those page types.
    $selected_page_types = get_term_meta($tag->term_id, 'selected_page_types');

    if (isset($selected_page_types[0]) && $selected_page_types[0]) {
        foreach ($selected_page_types[0] as $selected_page_type) {
            $p4_page_type = get_term_by('name', $selected_page_type, 'p4-page-type');
            if (!($p4_page_type instanceof \WP_Term)) {
                continue;
            }

            $cfc_args['post_types'] = $p4_page_type->term_id;
        }
    } else {
        // If none is selected, then display Publications by default (for backwards compatibility).
        $p4_page_type = get_term_by('slug', 'publication', 'p4-page-type');
        if ($p4_page_type instanceof \WP_Term) {
            $cfc_args['post_types'] = $p4_page_type->term_id;
        }
    }

    $campaign->add_block('covers', $cfc_args);

    // Convert old CampaignThumbnail block to Covers block[Campaign covers].
    $campaign->add_block(
        'covers',
        [
            'title' => __('Related Campaigns', 'planet4-master-theme'),
            'category_id' => $category->term_id
                ?? __('This Campaign is not assigned to an Issue', 'planet4-master-theme'),
            'cover_type' => '2', // Show Campaign covers.
        ]
    );

    // Get the image selected as background for the Subscribe section (HappyPoint block) inside the current Tag.
    $background = get_term_meta($tag->term_id, 'happypoint_attachment_id', true);
    $opacity = get_term_meta($tag->term_id, 'happypoint_bg_opacity', true);
    $options = get_option('planet4_options');

    $campaign->add_block(
        HappyPoint::BLOCK_NAME,
        [
            'mailing_list_iframe' => true,
            'id' => $background,
            'engaging_network_id' => $options['engaging_network_form_id'] ?? '',
            'opacity' => $opacity,
        ]
    );

    $campaign->view();
}
