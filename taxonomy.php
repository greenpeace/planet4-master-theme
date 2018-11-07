<?php
/**
 * The template for displaying Taxonomy pages.
 *
 * Used to display taxonomy-type pages
 *
 * Learn more: http://codex.wordpress.org/Template_Hierarchy
 *
 * @package  WordPress
 * @subpackage  Timber
 */

$templates = array( 'page_type.twig', 'index.twig' );

$context = Timber::get_context();
$context['page_type'] = get_queried_object();
$context['posts'] = Timber::get_posts();

$context['wp_title'] = $context['page_type']->name;

Timber::render( $templates, $context );
