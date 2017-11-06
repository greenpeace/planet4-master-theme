<?php
/**
 * Search results page
 *
 * Methods for TimberHelper can be found in the /lib sub-directory
 *
 * @package  WordPress
 * @subpackage  Timber
 * @since   Timber 0.1
 */

use Timber\Timber;
/**
 * Planet4 - Search functionality.
 */

global $wp_query;

$templates = array( 'search.twig', 'archive.twig', 'index.twig' );
$context   = Timber::get_context();
$context['sort_options'] = [
	'relevant'  => __( 'Most relevant', 'planet4-master-theme' ),
	'post_date' => __( 'Most recent', 'planet4-master-theme' ),
];

$default_sort  = 'relevant';
$selected_sort = filter_input( INPUT_GET, 'orderby', FILTER_SANITIZE_STRING );
$search        = get_search_query();

if ( ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
	$context['selected_sort'] = $default_sort;
} else {
	$context['selected_sort'] = $selected_sort;
}

/*
 * With no args passed to this call, Timber uses the main query which we filter for customisations via P4_Master_Site class.
 *
 * When customising this query, use filters on the main query to avoid bypassing SearchWP's handling of the query.
 */
$context['posts'] = Timber::get_posts();

$found_posts = $wp_query->found_posts;
$context['title']  = "$found_posts results for '$search'";
$context['domain'] = 'planet4-master-theme';

// Add pagination temporarily until we have a lazy loading solution. Use Timber::get_pagination() if we want a more customized one.
$context['pagination'] = [
	'screen_reader_text' => ' ',
];

$context['issues'] = get_categories( [
	'child_of' => get_category_by_slug( 'issues' )->term_id,
	'orderby'  => 'name',
	'order'    => 'ASC',
] );
$context['campaigns'] = [
	[
		'name'    => '#CampaignName1',
		'results' => 0,
	],
	[
		'name'    => '#CampaignName2',
		'results' => 0,
	],
	[
		'name'    => '#CampaignName3',
		'results' => 0,
	],
];
$context['categories'] = [
	[
		'name'    => '#1',
		'results' => 0,
	],
	[
		'name'    => '#2',
		'results' => 0,
	],
	[
		'name'    => '#3',
		'results' => 0,
	],
];
$context['content_types'] = [
	[
		'name'    => '#1',
		'results' => 0,
	],
	[
		'name'    => '#2',
		'results' => 0,
	],
	[
		'name'    => '#3',
		'results' => 0,
	],
];

Timber::render( $templates, $context );
