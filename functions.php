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
use P4\MasterTheme\Notifications\Slack;
use P4\MasterTheme\Post;
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
add_action(
	'rest_api_init',
	function () {
		Rest::register_endpoints();
	}
);

// Ensure no actions trigger a purge everything.
simple_value_filter( 'cloudflare_purge_everything_actions', [] );
// Remove the menu item to the Cloudflare page.
add_action(
	'admin_menu',
	function () {
		remove_submenu_page( 'options-general.php', 'cloudflare' );
	}
);
// remove_submenu_page does not prevent accessing the page. Add a higher prio action that dies instead.
add_action(
	'settings_page_cloudflare',
	function () {
		die( 'This page is blocked to prevent excessive cache purging.' );
	},
	1
);

/**
 * Hide core updates notification in the dashboard, to avoid confusion while an upgrade is already in progress.
 */
function hide_wp_update_nag() {
	remove_action( 'admin_notices', 'update_nag', 3 );
	remove_filter( 'update_footer', 'core_update_footer' );
}

add_action( 'admin_menu', 'hide_wp_update_nag' );

require_once 'load-class-aliases.php';

Loader::get_instance();

add_action(
	'notification/elements',
	static function () {
		notification_register_carrier( new Slack( 'slack', 'Slack' ) );
	}
);

// WP core's escaping logic doesn't take the case into account where a gradient is followed by a URL.
add_filter(
	'safecss_filter_attr_allow_css',
	function ( bool $allow_css, $css_test_string ) {
		// Short circuit in case the CSS is already allowed.
		// This filter only runs to catch the case where it's not allowed but should be.
		if ( $allow_css ) {
			return true;
		}

		$without_property = preg_replace( '/.*:/', '', $css_test_string );

		// Same regex as in WordPress core, except it matches anywhere in the string.
		// See https://github.com/WordPress/WordPress/blob/a5293aa581802197b0dd7c42813ba137708ad0e1/wp-includes/kses.php#L2438.
		$gradient_regex = '/(repeating-)?(linear|radial|conic)-gradient\(([^()]|rgb[a]?\([^()]*\))*\)/';

		// Check if a gradient is still present. The only case where $css_test_string can still have this present is if it
		// was missed by the faulty WP regex.
		if ( ! preg_match( $gradient_regex, $css_test_string ) ) {
			return $allow_css;
		}

		$without_gradient = preg_replace( $gradient_regex, '', trim( $without_property ) );

		return trim( $without_gradient, ', ' ) === '';
	},
	10,
	2
);
add_action(
	'wpml_after_update_attachment_texts',
	function ( $original_attachment_id, $translation ) {
		$original_sm_cloud = get_post_meta( $original_attachment_id, 'sm_cloud', true );
		update_post_meta( $translation->element_id, 'sm_cloud', $original_sm_cloud );
	},
	1,
	2
);

/**
 * This is not a column in WordPress by default, but is added by the Post Type Switcher plugin.
 * It's not needed for the plugin to work, and needlessly takes up space on pages where everything has the same post
 * type.
 *
 * Showing the field is only somewhat useful when using quick edit to switch a single post from the admin listing page.
 */
add_filter(
	'default_hidden_columns',
	function ( $hidden ) {
		$hidden[] = 'post_type';

		return $hidden;
	},
	10,
	1
);

// Might move later.
function register_more_blocks() {
	register_block_type( 'planet4-blocks/reading-time', [
		'render_callback' => [ Post::class, 'reading_time_block' ],
		'uses_context'    => [ 'postId' ],
	] );
}

add_action( 'init', 'register_more_blocks' );
