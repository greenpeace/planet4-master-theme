<?php
/**
 * Functions
 *
 * @package P4MT
 */

use Timber\Timber;

if ( ! class_exists( 'Timber' ) ) {
	add_action(
		'admin_notices',
		function() {
			printf(
				'<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="%s">Plugins menu</a></p></div>',
				esc_url( admin_url( 'plugins.php#timber' ) )
			);
		}
	);

	add_filter(
		'template_include',
		function( $template ) {
			return get_stylesheet_directory() . '/static/no-timber.html';
		}
	);

	return;
} else {
	// Enable Timber template cache unless this is a debug environment.
	if ( defined( 'WP_DEBUG' ) && is_bool( WP_DEBUG ) ) {
		Timber::$cache = ! WP_DEBUG;
	} else {
		Timber::$cache = true;
	}
}

require_once __DIR__ . '/classes/class-p4-loader.php';
P4_Loader::get_instance();
