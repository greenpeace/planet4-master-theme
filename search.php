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

$search = new P4_Search( get_search_query() );
$search->add_load_more();
//$search->add_pagination();
//$search->add_suggestions();
$search->view();
