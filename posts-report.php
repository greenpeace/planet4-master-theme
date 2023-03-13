<?php

/**
 * Renders posts reports template
 *
 * @package P4MT
 */

use Timber\Timber;

Timber::render([ 'posts_report.twig' ], [
    'spinner_gif' => get_template_directory_uri() . '/images/wpspin_light-2x.gif',
]);

require dirname(__FILE__) . '/underscore_templates/posts_reports.tpl.php';
