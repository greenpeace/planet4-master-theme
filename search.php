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

use P4\MasterTheme\ElasticSearch;

/**
 * Planet4 - Search functionality.
 */

// Limit access to GET method.
if ('GET' !== filter_input(INPUT_SERVER, 'REQUEST_METHOD')) {
    return;
}

$selected_sort = sanitize_text_field($_GET['orderby']);
$selected_filters = $_GET['f'] ?? ''; // phpcs:ignore
$filters = [];

// Handle submitted filter options.
if ($selected_filters && is_array($selected_filters)) {
    foreach ($selected_filters as $type_name => $filter_type) {
        if (! is_array($filter_type)) {
            continue;
        }
        foreach ($filter_type as $name => $filter_id) {
            $filters[ $type_name ][] = [
                'id' => $filter_id,
                'name' => $name,
            ];
        }
    }
}

$p4_search = new ElasticSearch();
$p4_search->load(trim(get_search_query()), $selected_sort, $filters);
$p4_search->add_load_more();
$p4_search->view();
