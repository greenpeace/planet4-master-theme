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

$search        = get_search_query();
$default_sort  = 'relevant';
$field         = 'search_results_sort';
$selected_sort = filter_input( INPUT_POST, $field, FILTER_SANITIZE_STRING );

if ( ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
	$context['selected_sort'] = $default_sort;
} else {
	$context['selected_sort'] = $selected_sort;
}

switch ( $context['selected_sort'] ) {
	case 'recent':
		$context['posts']  = Timber::get_posts( [
			's'       => $search,
			'orderby' => 'post_date',
			'order'   => 'DESC',
		] );
		break;
	default:
		$context['posts'] = Timber::get_posts( [
			's' => $search,
		] );
}

$found_posts = count( $context['posts'] );
$context['title']  = $found_posts . ' results for \'' . get_search_query() . '\'';

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

$context['domain'] = 'planet4-master-theme';

Timber::render( $templates, $context );
