<?php

/**
 * Renders posts reports template
 *
 * @package P4MT
 */

use Timber\Timber;

Timber::render([ 'posts_report.twig' ], [
    'spinner_gif' => get_template_directory_uri() . '/images/wpspin_light-2x.gif',
    'new_ia' => !empty(planet4_get_option('new_ia')),
]);

require dirname(__FILE__) . '/underscore_templates/posts_reports.tpl.php';
