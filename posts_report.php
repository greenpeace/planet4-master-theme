<?php

use Timber\Timber;

Timber::render( [ 'posts_report.twig' ], [] );

include dirname( __FILE__ ) . '/underscore_templates/posts-reports.tpl.php';
