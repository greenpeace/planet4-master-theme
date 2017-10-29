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

$default_sort  = 'relevant';
$selected_sort = filter_input( INPUT_GET, 'order', FILTER_SANITIZE_STRING );
$search        = get_search_query();

if ( ! in_array( $selected_sort, array_keys( $context['sort_options'] ), true ) ) {
	$context['selected_sort'] = $default_sort;
} else {
	$context['selected_sort'] = $selected_sort;
}

switch ( $context['selected_sort'] ) {
	case 'recent':
		$context['posts'] = Timber::get_posts( [
			// TODO - Find solution for Timber bug (see https://github.com/timber/timber/issues/935).
			// which does not include attachments to get_posts() results if we supply an array as an argument.
			's'       => $search,
			'orderby' => 'post_date',
			'order'   => 'DESC',
		] );
		break;
	default:
		$context['posts'] = Timber::get_posts();
}
$found_posts = count( $context['posts'] );

$context['title']  = "$found_posts results for '$search'";
$context['domain'] = 'planet4-master-theme';
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
