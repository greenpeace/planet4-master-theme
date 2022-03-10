<?php

declare(strict_types=1);

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Loader;

/**
 * Settings related to Posts comments.
 */
class Comments {
	/** @var string Option key */
	public const OPTIONS_KEY = 'planet4_comments';

	/* @var string Mobile tabs option key **/
	public const GDPR_CHECKBOX = 'gdpr_checkbox';

	/**
	 * Get the features options page settings.
	 *
	 * @return array Settings for the options page.
	 */
	public static function get_options_page(): array {
		return [
			'title'       => 'Comments',
			'description' => 'Options related to comments.',
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
				'id'   => self::GDPR_CHECKBOX,
				'name' => __( 'Display Opt-in checkbox', 'planet4-master-theme-backend' ),
				'desc' => __(
					'This will display an opt-in checkbox in the Comments form which will be mandatory for submitting the form (GDPR requirement).',
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
