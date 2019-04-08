<?php
/**
 * Functions
 *
 * @package P4MT
 */

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
}

require_once( __DIR__ . '/classes/class-p4-master-site.php' );

new P4_Master_Site(
	[
		'P4_Custom_Taxonomy',
		'P4_Campaigns',
		'P4_Post_Campaign',
		'P4_Campaign_Exporter',
		'P4_Settings',
		'P4_Control_Panel',
		'P4_Post_Report_Controller',
		'P4_Cookies',
	]
);
