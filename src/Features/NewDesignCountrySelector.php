<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class NewDesignCountrySelector extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'new_design_country_selector';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Enable new Country selector design', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Enable the new Country selector design as described in the <a href="https://p4-designsystem.greenpeace.org/05f6e9516/v/0/p/16a899-footer" target="_blank">design system</a>.<br/>This might break your child theme, depending on how you extended the main templates and CSS.<br/>Changing this option will take a bit of time, as it also attempts to clear the Cloudflare cache.',
			'planet4-master-theme-backend'
		);
	}

	/**
	 * @inheritDoc
	 */
	public static function show_toggle_production(): bool {
		return true;
	}
}
