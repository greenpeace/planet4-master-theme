<?php
/**
 * Renders posts reports template
 *
 * @package P4MT
 */

use Timber\Timber;

Timber::render( [ 'posts_report.twig' ], [] );

include dirname( __FILE__ ) . '/underscore_templates/posts_reports.tpl.php';
