<?php

/**
 * The template for displaying Archive pages.
 *
 * Used to display archive-type pages if nothing more specific matches a query.
 * For example, puts together date-based pages if no date.php file exists.
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * Methods for \Timber\Helper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.2
 */

use Timber\Timber;

$templates = [ 'archive.twig', 'index.twig' ];

$context = Timber::context();

$context['title'] = 'Archive';
if (is_day()) {
    $context['title'] = 'Archive: ' . get_the_date('D M Y');
} elseif (is_month()) {
    $context['title'] = 'Archive: ' . get_the_date('M Y');
} elseif (is_year()) {
    $context['title'] = 'Archive: ' . get_the_date('Y');
} elseif (is_tag()) {
    $context['title'] = single_tag_title('', false);
} elseif (is_category()) {
    $context['title'] = single_cat_title('', false);
    array_unshift($templates, 'archive-' . get_query_var('cat') . '.twig');
} elseif (is_post_type_archive()) {
    $context['title'] = post_type_archive_title('', false);
    array_unshift($templates, 'archive-' . get_post_type() . '.twig');
}

$context['posts'] = Timber::get_posts();

do_action('enqueue_google_tag_manager_script', $context);

Timber::render($templates, $context);
