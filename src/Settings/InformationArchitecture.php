<?php

declare(strict_types=1);

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Loader;

/**
 * Information architecture settings.
 *
 * @see https://jira.greenpeace.org/browse/PLANET-6467
 */
class InformationArchitecture {
	/** @var string Option key */
	public const OPTIONS_KEY = 'planet4_ia';

	/* @var string Mobile tabs option key **/
	public const MOBILE_TABS_MENU = 'mobile_tabs_menu';

	/**
	 * Get the features options page settings.
	 *
	 * @return array Settings for the options page.
	 */
	public static function get_options_page(): array {
		return [
			'title'       => 'Information architecture',
			'description' => 'These options are related to the new <a href="https://jira.greenpeace.org/browse/PLANET-6467" target="_blank">Information architecture development</a>.',
			'root_option' => self::OPTIONS_KEY,
			'fields'      => self::get_fields(),
			'add_scripts' => static function () {
				Loader::enqueue_versioned_script( '/admin/js/features_save_redirect.js' );
			},
		];
	}

	/**
	 * Get form fields.
	 *
	 * @return array  The fields.
	 */
	public static function get_fields(): array {
		$fields = [
			[
				'id'   => self::MOBILE_TABS_MENU,
				'name' => __( 'Enable mobile tabs menu', 'planet4-master-theme-backend' ),
				'desc' => __(
					'Display a sticky tabs menu visible on screen width smaller than 992px.',
					'planet4-master-theme-backend'
				),
				'type' => 'checkbox',
			],
		];

		return $fields;
	}

	/**
	 * Check whether an option is active.
	 *
	 * @param string $name Name of the option we're checking.
	 *
	 * @return bool Whether the option is active.
	 */
	public static function is_active( string $name ): bool {
		return ! empty( self::get( $name ) );
	}

	/**
	 * Return option value.
	 *
	 * @param string $name    Name of the option.
	 * @param mixed  $default Default value.
	 *
	 * @return mixed
	 */
	public static function get( string $name, $default = null ) {
		$options = get_option( self::OPTIONS_KEY );

		return isset( $options[ $name ] ) ? $options[ $name ] : $default;
	}
}
