<?php

declare(strict_types=1);

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Features\ActionPostType;
use P4\MasterTheme\Features\MobileTabsMenu;
use P4\MasterTheme\Loader;

/**
 * Information architecture settings.
 *
 * @see https://jira.greenpeace.org/browse/PLANET-6467
 */
class InformationArchitecture {
	/** @var string Option key */
	public const OPTIONS_KEY = 'planet4_ia';

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
		];
	}
}
