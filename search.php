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

global $wp_query;
$page = new SearchPage($wp_query);
$page->render();
