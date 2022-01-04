<?php

declare(strict_types=1);

namespace P4\MasterTheme\Api;

use WP_REST_Server;
use P4\MasterTheme\Settings as Planet4Settings;

/**
 * Instance settings API
 */
class Settings {
	/**
	 * Register endpoint to read settings.
	 *
	 * @example GET /wp-json/planet4/v1/settings/
	 */
	public static function register_endpoint(): void {
		register_rest_route(
			'planet4/v1',
			'settings',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => function () {
					return [
						'planet4_options' => self::list_planet4_settings(),
						'plugins'         => self::list_plugins(),
						'themes'          => self::list_themes(),
					];
				},
				'permission_callback' => function ( $request ) {
					$token    = $request->get_header( 'X-Auth-Token' );
					$expected = planet4_get_option( 'api_settings_token' );
					return ! empty( $token ) && $token === $expected;
				},
			]
		);
	}

	/**
	 * List of planet4 settings (key/value), sorted by language.
	 *
	 * @return array List of settings, by language.
	 */
	private static function list_planet4_settings(): array {
		$site_locale     = get_locale();
		$is_multilingual = function_exists( 'icl_get_languages' );

		$settings  = [];
		$languages = $is_multilingual
			? array_column( icl_get_languages(), 'code' )
			: [ $site_locale ];

		foreach ( $languages as $lang ) {
			do_action( 'wpml_switch_language', $lang );
			$local_options = get_option( Planet4Settings::KEY );
			ksort( $local_options );
			$settings[ $lang ] = $local_options;
		}

		do_action( 'wpml_switch_language', $site_locale );
		ksort( $settings );
		return $settings;
	}

	/**
	 * List of plugins installed and their active state.
	 *
	 * @return array List of plugins installed.
	 */
	private static function list_plugins(): array {
		$plugins = get_plugins();
		$list    = [];
		foreach ( $plugins as $key => &$plugin ) {
			$list[ $key ] = [
				'name'    => $plugin['Name'],
				'version' => $plugin['Version'],
				'active'  => is_plugin_active( $key ),
			];
		}
		ksort( $list );
		return $list;
	}

	/**
	 * List of themes installed.
	 *
	 * @return array List of themes installed.
	 */
	private static function list_themes(): array {
		$themes = wp_get_themes();
		$list   = [];
		foreach ( $themes as $name => $theme ) {
			$list[ $name ] = [
				'name'    => $theme->name,
				'version' => $theme->version,
				'dir'     => $theme->get_stylesheet_directory(),
				'parent'  => $theme->parent_theme,
			];
		}
		ksort( $list );
		return $list;
	}
}
