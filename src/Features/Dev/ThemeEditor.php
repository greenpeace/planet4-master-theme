<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ThemeEditor extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'theme_editor';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Theme editor (experimental)', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Enable CSS variables based theme editor for logged in users.',
			'planet4-master-theme-backend'
		);
	}
}
