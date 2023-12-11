<?php

/**
 * The template for displaying Taxonomy pages (Post types, Action types).
 *
 * Used to display taxonomy-type pages
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package  WordPress
 * @subpackage  Timber
 */

use P4\MasterTheme\Post;
use P4\MasterTheme\ActionPage;
use Timber\Timber;

$templates = [ 'taxonomy.twig', 'index.twig' ];

$context = Timber::get_context();
$taxonomy = get_queried_object();
$context['taxonomy'] = $taxonomy;
$context['wp_title'] = $taxonomy->name;
$context['canonical_link'] = home_url($wp->request);
$context['og_type'] = 'website';
$context['og_description'] = $taxonomy->description;

if (!empty(planet4_get_option('new_ia'))) {
    $context['page_category'] = 'Listing Page';
    $template = file_get_contents(get_template_directory() . "/parts/query-listing-page.html");
    $content = do_blocks($template);
    $context['listing_page_content'] = $content;
    $news_page = (int) get_option('page_for_posts');
    if ($news_page) {
        $news_page_link = get_permalink($news_page);
        $context['news_page_link'] = $news_page_link;
    }
    Timber::render($templates, $context);
    exit();
}

$allowed_post_types = [
    'post',
    'page',
    'campaign',
    'attachment',
    (bool) planet4_get_option('new_ia') ? ActionPage::POST_TYPE : null,
];

$post_args = [
    'posts_per_page' => 10,
    'post_type' => $allowed_post_types,
    'paged' => 1,
    'has_password' => false, // Skip password protected content.
    'tax_query' => [
        [
            'taxonomy' => $taxonomy->taxonomy,
            'field' => 'slug',
            'terms' => $taxonomy->slug,
        ],
    ],
];

$context['page_category'] = 'Post Type Page';
$context['custom_body_classes'] = 'tax-p4-page-type';

if (get_query_var('page')) {
    $templates = [ 'tease-taxonomy-post.twig' ];
    $post_args['paged'] = get_query_var('page');
    $pagetype_posts = new \Timber\PostQuery($post_args, Post::class);
    foreach ($pagetype_posts as $pagetype_post) {
        $context['post'] = $pagetype_post;
        Timber::render($templates, $context);
    }
} else {
    $pagetype_posts = new \Timber\PostQuery($post_args, Post::class);
    $context['posts'] = $pagetype_posts;
    Timber::render($templates, $context);
}
