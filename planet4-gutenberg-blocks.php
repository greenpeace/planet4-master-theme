<?php
/**
 * Plugin Name: Planet4 - Gutenberg Blocks
 * Description: Contains the Gutenberg blocks that are used by Planet4 project.
 * Plugin URI: http://github.com/greenpeace/planet4-plugin-gutenberg-blocks
 * Version: 0.1
 * Php Version: 7.0
 *
 * Author: Greenpeace International
 * Author URI: http://www.greenpeace.org/
 * Text Domain: planet4-blocks
 *
 * License:     GPLv3
 * Copyright (C) 2018 Greenpeace International
 *
 * @package P4GBKS
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Direct access is forbidden !' );


/*
========================
	C O N S T A N T S
========================
*/
if ( ! defined( 'P4GBKS_REQUIRED_PHP' ) ) {
	define( 'P4GBKS_REQUIRED_PHP', '7.0' );
}
if ( ! defined( 'P4GBKS_REQUIRED_PLUGINS' ) ) {
	define(
		'P4GBKS_REQUIRED_PLUGINS',
		[
			'timber' => [
				'min_version' => '1.9.0',
				'rel_path'    => 'timber-library/timber.php',
			],
		]
	);
}
if ( ! defined( 'P4GBKS_PLUGIN_BASENAME' ) ) {
	define( 'P4GBKS_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'P4GBKS_PLUGIN_DIRNAME' ) ) {
	define( 'P4GBKS_PLUGIN_DIRNAME', dirname( P4GBKS_PLUGIN_BASENAME ) );
}
if ( ! defined( 'P4GBKS_PLUGIN_DIR' ) ) {
	define( 'P4GBKS_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . P4GBKS_PLUGIN_DIRNAME );
}
if ( ! defined( 'P4GBKS_PLUGIN_URL' ) ) {
	define( 'P4GBKS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'P4GBKS_PLUGIN_NAME' ) ) {
	define( 'P4GBKS_PLUGIN_NAME', 'Planet4 - Gutenberg Blocks' );
}
if ( ! defined( 'P4GBKS_PLUGIN_SHORT_NAME' ) ) {
	define( 'P4GBKS_PLUGIN_SHORT_NAME', 'Blocks' );
}
if ( ! defined( 'P4GBKS_PLUGIN_SLUG_NAME' ) ) {
	define( 'P4GBKS_PLUGIN_SLUG_NAME', 'blocks' );
}
if ( ! defined( 'P4GBKS_INCLUDES_DIR' ) ) {
	define( 'P4GBKS_INCLUDES_DIR', P4GBKS_PLUGIN_DIR . '/templates/' );
}
if ( ! defined( 'P4GBKS_TEMPLATE_OVERRIDE_SUBDIR' ) ) {
	define( 'P4GBKS_TEMPLATE_OVERRIDE_SUBDIR', '/templates/plugins/planet4-plugin-gutenberg-blocks/includes/' );
}
if ( ! defined( 'P4GBKS_ADMIN_DIR' ) ) {
	define( 'P4GBKS_ADMIN_DIR', plugins_url( P4GBKS_PLUGIN_DIRNAME . '/admin/' ) );
}
if ( ! defined( 'P4GBKS_LANGUAGES' ) ) {
	define(
		'P4GBKS_LANGUAGES',
		[
			'en_US' => 'English',
			'el_GR' => 'Ελληνικά',
		]
	);
}

if ( ! defined( 'P4GBKS_ALLOWED_PAGETYPE' ) ) {
	define( 'P4GBKS_ALLOWED_PAGETYPE', [ 'page', 'campaign' ] );
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	define( 'WP_UNINSTALL_PLUGIN', P4GBKS_PLUGIN_BASENAME );
}

require_once __DIR__ . '/classes/class-loader.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/*
==========================
	F I L T E R
==========================
*/

const POST_BLOCK_TYPES = [
	'planet4-blocks/counter',
	'planet4-blocks/gallery',
	'planet4-blocks/timeline',
	'planet4-blocks/take-action-boxout',
];

// pages allow all block types.
const PAGE_BLOCK_TYPES = [
	'planet4-blocks/articles',
	'planet4-blocks/carousel-header',
	'planet4-blocks/columns',
	'planet4-blocks/cookies',
	'planet4-blocks/counter',
	'planet4-blocks/covers',
	'planet4-blocks/gallery',
	'planet4-blocks/happypoint',
	'planet4-blocks/media',
	'planet4-blocks/social-media',
	'planet4-blocks/split-two-columns',
	'planet4-blocks/submenu',
	'planet4-blocks/timeline',
];

// campaigns allow all block types.
const CAMPAIGN_BLOCK_TYPES = [
	'planet4-blocks/articles',
	'planet4-blocks/carousel-header',
	'planet4-blocks/columns',
	'planet4-blocks/cookies',
	'planet4-blocks/counter',
	'planet4-blocks/covers',
	'planet4-blocks/gallery',
	'planet4-blocks/happypoint',
	'planet4-blocks/media',
	'planet4-blocks/social-media',
	'planet4-blocks/split-two-columns',
	'planet4-blocks/submenu',
	'planet4-blocks/timeline',
];

/**
 * Allowed block types based on post type
 *
 * @param array  $allowed_block_types array of allowed block types.
 * @param object $post current post.
 *
 * @return true if all blocks allowed, false if none or an array of allowed blocks
 */
function set_allowed_block_types( $allowed_block_types, $post ) {
	$wordpress_blocks = [
		'core/paragraph',
		'core/heading',
		'core/image',
		// 'core/gallery' - functionality replaced by P4 galleries.
		'core/list',
		'core/quote', // TODO: Styling or removal.
		// 'core/audio' // removed, not needed.
		'core/cover',
		'core/file',
		// 'core/video' - TODO: Decision. Ideally only allow embedded video.
		// 'core/preformatted' // removed, not needed.
		// 'core/code' - functionality not needed and not styled.
		'core/html',
		'core/table', // TODO: Styling.
		// 'core/pullquote' - removed, normal quote element is available.
		// 'core/verse' - removed, not needed, not styled.
		'core/button', // TODO: Styling.
		// 'core/media-text' // removed, not needed.
		// 'core/more' - removed, not needed.
		// 'core/nextpage' - removed, not needed.
		'core/separator', // TODO: Styling.
		// 'core/spacer' - removed. TODO: Provide our own spacer that has less options.
		'core/shortcode',
		// 'core/archives' - removed, not needed.
		// 'core/categories' - removed, not needed.
		// 'core/latest-comments' - removed, not needed.
		// 'core/latest-posts' // removed, functionality replaced by P4 article list.
		'core/embed',
		'core-embed/twitter',
		'core-embed/youtube',
		'core-embed/facebook',
		'core-embed/instagram',
		'core-embed/wordpress',
		'core-embed/soundcloud',
		'core-embed/spotify',
		'core-embed/flickr',
		'core-embed/vimeo',
		'core-embed/animoto',
		'core-embed/cloudup',
		'core-embed/collegehumor',
		'core-embed/dailymotion',
		'core-embed/funnyordie',
		'core-embed/hulu',
		'core-embed/imgur',
		'core-embed/issuu',
		'core-embed/kickstarter',
		'core-embed/meetup-com',
		'core-embed/mixcloud',
		'core-embed/photobucket',
		'core-embed/polldaddy',
		'core-embed/reddit',
		'core-embed/reverbnation',
		'core-embed/screencast',
		'core-embed/scribd',
		'core-embed/slideshare',
		'core-embed/smugmug',
		'core-embed/speaker',
		'core-embed/ted',
		'core-embed/tumblr',
		'core-embed/videopress',
		'core-embed/wordpress-tv',
	];

	$allowed_p4_block_types = [
		'post'     => POST_BLOCK_TYPES,
		'page'     => PAGE_BLOCK_TYPES,
		'campaign' => CAMPAIGN_BLOCK_TYPES,
	];

	$allowed_block_types = array_merge( $wordpress_blocks, $allowed_p4_block_types[ $post->post_type ] );

	return $allowed_block_types;
}

add_filter( 'allowed_block_types', 'set_allowed_block_types', 10, 2 );

/*
==========================
	L O A D  P L U G I N
==========================
*/
P4GBKS\Loader::get_instance(
	[
		// --- Add here your own Block Controller ---
		// DEPRECATED: Blocks could be registered inside Loader class
		// 'P4GBKS\Controllers\Blocks\NewCovers_Controller'
	],
	'P4GBKS\Views\View'
);
