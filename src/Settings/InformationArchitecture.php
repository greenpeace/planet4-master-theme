<?php

declare(strict_types=1);

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Features\ActionPostType;
use P4\MasterTheme\Features\MobileTabsMenu;
use P4\MasterTheme\Features\DropdownMenu;
use P4\MasterTheme\Features\PostPageCategoryLinks;
use P4\MasterTheme\Features\HideListingPagesBackground;
use P4\MasterTheme\Loader;

/**
 * Information architecture settings.
 *
 * @see https://jira.greenpeace.org/browse/PLANET-6467
 */
class InformationArchitecture {
	/** @var string Option key */
	public const OPTIONS_KEY = 'planet4_ia';

	/* @var string feature flag of action page type option key **/
	public const ACTION_POST_TYPE = 'action_post_type';

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
		return [
			MobileTabsMenu::get_cmb_field(),
			ActionPostType::get_cmb_field(),
			DropdownMenu::get_cmb_field(),
			PostPageCategoryLinks::get_cmb_field(),
			HideListingPagesBackground::get_cmb_field(),
		];
	}

	/**
	 * Check whether an option is active.
	 *
	 * @param string $name Name of the option we're checking.
	 *
	 * @return bool Whether the option is active.
	 */
	public static function is_active( string $name ): bool {
		$options = get_option( self::OPTIONS_KEY );

		return isset( $options[ $name ] ) ? 'on' === $options[ $name ] : false;
	}
}
