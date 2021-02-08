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
}

// Enable Timber template cache unless this is a debug environment.
Timber::$cache = defined( 'WP_DEBUG' ) && is_bool( WP_DEBUG ) ? ! WP_DEBUG : true;

// Prevent posts from being saved if they have the "locked" meta key. There are plugins for this too, but usually they
// still allow admins to make changes.
if ( defined( 'LOCK_TEST_CONTENT' ) && LOCK_TEST_CONTENT ) {
	add_filter(
		'user_has_cap',
		static function ( $allcaps, $cap, $args ) {
			// Post IDs are always in the third argument, nothing to do if that is empty.
			// Check if test content locking is enabled.
			if ( ! isset( $args[2] ) ) {
				return $allcaps;
			}

			[ $action, $user_id, $post_id ] = $args;

			if (
				empty( $allcaps['edit_posts'] )
				|| ( ! in_array( $action, [ 'edit_post', 'delete_post' ], true ) )
			) {
				return $allcaps;
			}

			// We can create a separate export of all posts in defaultcontent that have this meta key.
			// Then we can identify posts to be locked by this meta as well.
			$is_locked = get_post_meta( $post_id, 'test-content', true );

			if ( $is_locked ) {
				// There could be more than one capability being checked inside $cap.
				// The action requires all of them to be true, so we only need to take away the first to prevent the action.
				$allcaps[ $cap[0] ] = false;
			}

			return $allcaps;
		},
		10,
		3
	);
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

require_once 'load-class-aliases.php';

Loader::get_instance();
