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
		return __( 'New Country selector design', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Enables the new Country selector design as described in the <a href="https://p4-designsystem.greenpeace.org/05f6e9516/v/0/p/16a899-footer" target="_blank">design system</a>.<br>This might need changes in your child theme code.',
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
