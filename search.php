<?php

/**
 * Search results page
 */

use P4\MasterTheme\Search\SearchPage;

/**
 * Planet4 - Search functionality.
 */

// Limit access to GET method.
if ('GET' !== filter_input(INPUT_SERVER, 'REQUEST_METHOD')) {
    return;
}

$selected_sort = empty($_GET['orderby'])
                ? null : sanitize_text_field($_GET['orderby']);
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

global $wp_query;
$page = new SearchPage($wp_query);
$page->render();

