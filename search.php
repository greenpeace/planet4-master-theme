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
	'relevant' => __( 'Most relevant', 'planet4-master-theme' ),
	'recent'   => __( 'Most recent', 'planet4-master-theme' ),
];

$default_sort  = 'relevant';
$selected_sort = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );
$search        = get_search_query();

if ( ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
	$context['selected_sort'] = $default_sort;
} else {
	$context['selected_sort'] = $selected_sort;
}

/*
  The issue seems like it is related with Timber and SearchWP not collaborating very well here. After investigating this we found that
  If we do not pass an argument to Timber::get_posts() then it falls back to the main WP_Query which works with SearchWP.
  If we pass an argument to Timber::get_posts() then it creates a subquery and SearchWP is not aware of that and therefore we do not get attachemnts included in search results.
  A solution is to proceed without passing query options to get_posts at all
  and instead use the `pre_get_posts` hook to set the options to the main WP_Query directly.
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
