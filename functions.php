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
} else {
	// Enable Timber template cache unless this is a debug environment.
	if ( defined( 'WP_DEBUG' ) && is_bool( WP_DEBUG ) ) {
		Timber::$cache = ! WP_DEBUG;
	} else {
		Timber::$cache = true;
	}
}

require_once( __DIR__ . '/classes/class-p4-master-site.php' );

$services = [
	'P4_Metabox_Register',
	'P4_Custom_Taxonomy',
	'P4_Campaigns',
	'P4_Post_Campaign',
	'P4_Settings',
	'P4_Control_Panel',
	'P4_Post_Report_Controller',
	'P4_Cookies',
	'P4_Dev_Report',
];

if ( is_admin() ) {
	// Load `P4_Campaign_Exporter` class on admin campaign listing page and campaign export only.
	if ( 'campaign' === filter_input( INPUT_GET, 'post_type', FILTER_SANITIZE_STRING ) || 'export_data' === filter_input( INPUT_GET, 'action', FILTER_SANITIZE_STRING ) ) {
		$services[] = 'P4_Campaign_Exporter';
	}

	// Load `P4_Campaign_Importer` class on admin campaign import only.
	// phpcs:disable
	if ( 'wordpress' === filter_input( INPUT_GET, 'import', FILTER_SANITIZE_STRING ) ) {
	// phpcs:enable
		$services[] = 'P4_Campaign_Importer';
	}
}

new P4_Master_Site(
	$services
);
