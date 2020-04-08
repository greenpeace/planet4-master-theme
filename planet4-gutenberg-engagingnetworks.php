<?php
/**
 * Plugin Name: Planet4 - Gutenberg Engaging Networks
 * Description: Contains the Gutenberg blocks that are used by Planet4 project.
 * Plugin URI: http://github.com/greenpeace/planet4-plugin-engaging-networks
 * Version: 0.16
 * Php Version: 7.0
 *
 * Author: Greenpeace International
 * Author URI: http://www.greenpeace.org/
 * Text Domain: planet4-engagingnetworks
 *
 * License:     GPLv3
 * Copyright (C) 2018 Greenpeace International
 *
 * @package P4GEN
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Direct access is forbidden !' );


/*
========================
	C O N S T A N T S
========================
*/
if ( ! defined( 'P4GEN_REQUIRED_PHP' ) ) {
	define( 'P4GEN_REQUIRED_PHP', '7.0' );
}
if ( ! defined( 'P4GEN_REQUIRED_PLUGINS' ) ) {
	define(
		'P4GEN_REQUIRED_PLUGINS',
		[
			'timber' => [
				'min_version' => '1.9.0',
				'rel_path'    => 'timber-library/timber.php',
			],
		]
	);
}
if ( ! defined( 'P4GEN_PLUGIN_BASENAME' ) ) {
	define( 'P4GEN_PLUGIN_BASENAME', plugin_basename( __FILE__ ) );
}
if ( ! defined( 'P4GEN_PLUGIN_DIRNAME' ) ) {
	define( 'P4GEN_PLUGIN_DIRNAME', dirname( P4GEN_PLUGIN_BASENAME ) );
}
if ( ! defined( 'P4GEN_PLUGIN_DIR' ) ) {
	define( 'P4GEN_PLUGIN_DIR', WP_PLUGIN_DIR . '/' . P4GEN_PLUGIN_DIRNAME );
}
if ( ! defined( 'P4GEN_PLUGIN_URL' ) ) {
	define( 'P4GEN_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
}
if ( ! defined( 'P4GEN_PLUGIN_NAME' ) ) {
	define( 'P4GEN_PLUGIN_NAME', 'Planet4 - Engaging Networks' );
}
if ( ! defined( 'P4GEN_PLUGIN_SHORT_NAME' ) ) {
	define( 'P4GEN_PLUGIN_SHORT_NAME', 'EngagingNetworks' );
}
if ( ! defined( 'P4GEN_PLUGIN_SLUG_NAME' ) ) {
	define( 'P4GEN_PLUGIN_SLUG_NAME', 'engagingnetworks' );
}
if ( ! defined( 'P4GEN_INCLUDES_DIR' ) ) {
	define( 'P4GEN_INCLUDES_DIR', P4GEN_PLUGIN_DIR . '/templates/' );
}
if ( ! defined( 'P4GEN_TEMPLATE_OVERRIDE_SUBDIR' ) ) {
	define( 'P4GEN_TEMPLATE_OVERRIDE_SUBDIR', '/templates/plugins/planet4-plugin-gutenberg-engagingnetworks/includes/' );
}
if ( ! defined( 'P4GEN_ADMIN_DIR' ) ) {
	define( 'P4GEN_ADMIN_DIR', plugins_url( P4GEN_PLUGIN_DIRNAME . '/admin/' ) );
}
if ( ! defined( 'P4GEN_LANGUAGES' ) ) {
	define(
		'P4GEN_LANGUAGES',
		[
			'en_US' => 'English',
			'el_GR' => 'Ελληνικά',
		]
	);
}

if ( ! defined( 'P4GEN_ALLOWED_PAGETYPE' ) ) {
	define( 'P4GEN_ALLOWED_PAGETYPE', [ 'page', 'campaign' ] );
}
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	define( 'WP_UNINSTALL_PLUGIN', P4GEN_PLUGIN_BASENAME );
}
if ( ! defined( 'P4_REST_SLUG' ) ) {
	define( 'P4_REST_SLUG', 'planet4-engaging-networks' );
}

add_filter( 'timber/twig', 'p4_en_forms_twig_filters' );

/**
 * Adds functionality to Twig.
 *
 * @param \Twig\Environment $twig The Twig environment.
 * @return \Twig\Environment
 */
function p4_en_forms_twig_filters( $twig ) {
	// Adding functions as filters.
	$twig->addFilter(
		new Twig_SimpleFilter(
			'object_to_array',
			function ( $std_class_object ) {
				$response = [];
				foreach ( $std_class_object as $key => $value ) {
					$response[ $key ] = $value;
				}
				return $response;
			}
		)
	);

	return $twig;
}

require_once __DIR__ . '/classes/class-loader.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';

/**
 * Allow EN block
 *
 * @param array  $allowed_block_types array of allowed block types.
 * @param object $post current post.
 *
 * @return true if all blocks allowed, false if none or an array of allowed blocks
 */
function allow_en_block( $allowed_block_types, $post ) {

	$allowed_en_block_type = [
		'post'     => [],
		'page'     => [ 'planet4-blocks/enform' ],
		'campaign' => [ 'planet4-blocks/enform' ],
	];

	return array_merge( $allowed_block_types, $allowed_en_block_type[ $post->post_type ] );
}

add_filter( 'allowed_block_types', 'allow_en_block', 11, 2 );

/*
==========================
	L O A D  P L U G I N
==========================
*/
P4GEN\Loader::get_instance(
	[
		'P4GEN\Controllers\Menu\Enform_Post_Controller',
		'P4GEN\Controllers\Menu\Settings_Controller',
		'P4GEN\Controllers\Api\Rest_Controller',
	],
	'P4GEN\Views\View'
);
