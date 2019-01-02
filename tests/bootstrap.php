<?php
/**
 * PHPUnit bootstrap file
 *
 * @package Planet4_Master_Theme
 */

$_tests_dir = getenv( 'WP_TESTS_DIR' );
if ( ! $_tests_dir ) {
	$_tests_dir = rtrim( sys_get_temp_dir(), '/\\' ) . '/wordpress-tests-lib';
}

if ( ! file_exists( $_tests_dir . '/includes/functions.php' ) ) {
	throw new Exception( "Could not find $_tests_dir/includes/functions.php, have you run bin/install-wp-tests.sh ?" );
}

// Give access to tests_add_filter() function.
require_once $_tests_dir . '/includes/functions.php';

function _register_theme() {

	$theme_dir     = dirname( __DIR__ );
	$current_theme = basename( $theme_dir );
	$theme_root    = dirname( $theme_dir );

	add_filter( 'theme_root', function () use ( $theme_root ) {
		return $theme_root;
	} );

	register_theme_directory( $theme_root );

	add_filter( 'pre_option_template', function () use ( $current_theme ) {
		return $current_theme;
	} );
	add_filter( 'pre_option_stylesheet', function () use ( $current_theme ) {
		return $current_theme;
	} );

	$plugins_dir = dirname( __FILE__ ) . '/../../../plugins';
	$timber      = $plugins_dir . '/timber/timber.php';
	if ( file_exists( $timber ) ) {
		require_once( $timber );
	} else {
		$timber_library = $plugins_dir . '/timber-library/timber.php';
		if ( file_exists( $timber_library ) ) {
			require_once( $timber_library );
		}
	}
}

tests_add_filter( 'muplugins_loaded', '_register_theme' );


// Start up the WP testing environment.
require $_tests_dir . '/includes/bootstrap.php';
// Include the composer autoloader.
$autoloader = require dirname( __DIR__ ) . '/vendor/autoload.php';
require_once 'p4-testcase.php';
