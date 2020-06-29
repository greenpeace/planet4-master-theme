<?php
/**
 * Functions
 *
 * @package P4MT
 */

// Composer install might have happened inside the master-theme directory, instead of the app root. Specifically for
// php lint and unit test job this is the case.
// Also this helps with local development environments that pulled in changes for master-theme but did not rerun
// base-fork's composer installation, which is currently tricky to do as it puts the fetched versions of master theme
// and the plugins into the theme and plugin folders, which might be messy if you have changes there.
// With this fallback for tests in place, you can just run composer dump autoload in master-theme.
// Probably there's a better way to handle this, but for now let's try load both.
if ( file_exists( __DIR__ . '/vendor/autoload.php' ) ) {
	require_once __DIR__ . '/vendor/autoload.php';
} else {
	require_once __DIR__ . '/../../../../vendor/autoload.php';
}

/**
 * A simpler way to add a filter that only returns a static value regardless of the input.
 *
 * @param string   $filter_name The WordPress filter.
 * @param mixed    $value The value to be returned by the filter.
 * @param int|null $priority The priority for the fitler.
 *
 * @return void
 */
function simple_value_filter( string $filter_name, $value, $priority = null ): void {
	add_filter(
		$filter_name,
		static function () use ( $value ) {
			return $value;
		},
		$priority,
		0
	);
}

/**
 * Generate a bunch of placeholders for use in an IN query.
 * Unfortunately WordPress doesn't offer a way to do bind IN statement params, it would be a lot easier if we could pass
 * the array to wpdb->prepare as a whole.
 *
 * @param array  $items The items to generate placeholders for.
 * @param int    $start_index The start index to use for creating the placeholders.
 * @param string $type The type of value.
 *
 * @return string The generated placeholders string.
 */
function generate_list_placeholders( array $items, int $start_index, $type = 'd' ): string {
	$placeholders = [];
	foreach ( range( $start_index, count( $items ) + $start_index - 1 ) as $i ) {
		$placeholder = "%{$i}\${$type}";
		// Quote it if it's a string.
		if ( 's' === $type ) {
			$placeholder = "'{$placeholder}'";
		}
		$placeholders[] = $placeholder;
	}

	return implode( ',', $placeholders );
}

/**
 * Wrapper function around cmb2_get_option.
 *
 * @param string $key Options array key.
 * @param bool   $default The default value to use if the options is not set.
 * @return mixed Option value.
 */
function planet4_get_option( $key = '', $default = null ) {
	$options = get_option( 'planet4_options' );

	return $options[ $key ] ?? $default;
}

use P4\MasterTheme\ImageArchive\Rest;
use P4\MasterTheme\Loader;
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
add_action( 'rest_api_init', function () {
	Rest::register_endpoints();
} );


Loader::get_instance();

require_once 'load-class-aliases.php';
