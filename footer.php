<?php
/**
 * Third party plugins that hijack the theme will call wp_footer() to get the footer template.
 * We use this to end our output buffer (started in header.php) and render into the view/page-plugin.twig template.
 *
 * If you're not using a plugin that requries this behavior (ones that do include Events Calendar Pro and
 * WooCommerce) you can delete this file and header.php
 *
 * @package P4MT
 */

$timber_context = $GLOBALS['timberContext'];
try {
	if ( ! isset( $timber_context ) ) {
		throw new \Exception( 'Timber context not set in footer.' );
	}
} catch ( Exception $e ) {
	die( 'Error: ' . $e->getMessage() );
}
$timber_context['content'] = ob_get_contents();
ob_end_clean();
$templates = [ 'page-plugin.twig' ];
Timber::render( $templates, $timber_context );
