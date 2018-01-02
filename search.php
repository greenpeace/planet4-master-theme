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
wp_enqueue_script( 'search', get_template_directory_uri() . '/assets/js/search.js', [], '0.1.0', true );
if ( is_main_query() && is_search() ) {
	if ( 'GET' === $_SERVER['REQUEST_METHOD'] ) {
		$selected_sort = $_GET['orderby'];

		// Handle submitted filter options.
		if ( is_array( $_GET['f'] ) ) {
			foreach ( $_GET['f'] as $type => $filter_type ) {
				foreach ( $filter_type as $name => $id ) {
					$filters[ $type ][] = [
						'id'   => $id,
						'name' => $name,
					];
				}
			}
		}
		$search = new P4_Search( get_search_query(), $selected_sort, $filters );
		$search->add_load_more();
		$search->view();
	}
}
