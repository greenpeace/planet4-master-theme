<?php
/**
 * Plugin Name: Planet4 - Gpi Media Library
 * Description: Connects Planet4 with the Media Library platform.
 * Plugin URI: http://github.com/greenpeace/planet4-plugin-medialibrary
 * Version: 0.1.1
 * Php Version: 7.0
 *
 * Author: Greenpeace International
 * Author URI: http://www.greenpeace.org/
 * Text Domain: planet4-medialibrary
 *
 * License:     GPLv3
 * Copyright (C) 2018 Greenpeace International
 */


/**
 * Followed WordPress plugins best practices from https://developer.wordpress.org/plugins/the-basics/best-practices/
 * Followed WordPress-Core coding standards https://make.wordpress.org/core/handbook/best-practices/coding-standards/php/
 * Followed WordPress-VIP coding standards https://vip.wordpress.com/documentation/code-review-what-we-look-for/
 * Added namespacing and PSR-4 auto-loading.
 */

// Exit if accessed directly.
defined( 'ABSPATH' ) || die( 'Direct access is forbidden !' );


/* ========================
      C O N S T A N T S
   ======================== */
if ( ! defined( 'P4ML_REQUIRED_PHP' ) )        define( 'P4ML_REQUIRED_PHP',       '7.0' );
if ( ! defined( 'P4ML_REQUIRED_PLUGINS' ) )    define( 'P4ML_REQUIRED_PLUGINS',   [
	'timber' => [
		'min_version' => '1.3.0',
		'rel_path'    => 'timber-library/timber.php',
	],
] );
if ( ! defined( 'P4ML_PLUGIN_BASENAME' ) )     define( 'P4ML_PLUGIN_BASENAME',    plugin_basename( __FILE__ ) );
if ( ! defined( 'P4ML_PLUGIN_DIRNAME' ) )      define( 'P4ML_PLUGIN_DIRNAME',     dirname( P4ML_PLUGIN_BASENAME ) );
if ( ! defined( 'P4ML_PLUGIN_DIR' ) )          define( 'P4ML_PLUGIN_DIR',         WP_PLUGIN_DIR . '/' . P4ML_PLUGIN_DIRNAME );
if ( ! defined( 'P4ML_PLUGIN_NAME' ) )         define( 'P4ML_PLUGIN_NAME',        'Planet4 - Gpi Media Library' );
if ( ! defined( 'P4ML_PLUGIN_SHORT_NAME' ) )   define( 'P4ML_PLUGIN_SHORT_NAME',  'GpiMediaLibrary' );
if ( ! defined( 'P4ML_PLUGIN_SLUG_NAME' ) )    define( 'P4ML_PLUGIN_SLUG_NAME',   'gpimedialibrary' );
if ( ! defined( 'P4ML_INCLUDES_DIR' ) )        define( 'P4ML_INCLUDES_DIR',       P4ML_PLUGIN_DIR . '/includes/' );
if ( ! defined( 'P4ML_ADMIN_DIR' ) )           define( 'P4ML_ADMIN_DIR',          plugins_url( P4ML_PLUGIN_DIRNAME . '/admin/' ) );
if ( ! defined( 'P4ML_LANGUAGES' ) )           define( 'P4ML_LANGUAGES',          [
	'en_US' => 'English',
	'el_GR' => 'Ελληνικά',
] );
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) )      define( 'WP_UNINSTALL_PLUGIN',     P4ML_PLUGIN_BASENAME );



require_once __DIR__ . '/vendor/autoload.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';


/* ==========================
      L O A D  P L U G I N
   ========================== */
P4ML\Loader::get_instance( [
	'P4ML\Controllers\Menu\Settings_Controller',
	'P4ML\Controllers\Tab\GPI_Media_Library_Controller',
], 'P4ML\Views\View' );
