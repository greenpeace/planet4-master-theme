<?php /* Template Name: Take Action */

$page = new P4_Page();

//If there is a background image, use it instead of the default
$page->context['background_image']  = $page->page_meta_data['p4_background_image'][0];

Timber::render( [ 'page-' . $page->post->post_name . '.twig', 'page-take-action.twig', 'page.twig' ], $page->context );
