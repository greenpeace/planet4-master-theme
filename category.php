<?php

/**
 * The template for displaying Categories.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package  WordPress
 * @subpackage  Timber
 */

use P4\MasterTheme\Post;
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
    $template = file_get_contents(get_template_directory() . "/parts/query-listing-page.html");
    $content = do_blocks($template);
    $context['listing_page_content'] = $content;
    $context['page_category'] = 'Listing Page';
    Timber::render($templates, $context);
    exit();
}

$post_args = [
    'cat' => $taxonomy->term_id,
    'posts_per_page' => 10,
    'post_type' => 'post',
    'paged' => 1,
    'has_password' => false, // Skip password protected content.
];
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
