<?php

global $post;

/**
 * Template Name: Example Page
 */

use Timber\Timber;

$context = Timber::context();

Timber::render('example.twig', $context);
