<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\InformationArchitecture;

/**
 * @see description().
 */
class DropdownMenu extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'dropdown_menu';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Enable dropdown menu', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Display subitems through a list.',
			'planet4-master-theme-backend'
		);
	}

	/**
	 * @inheritDoc
	 */
	protected static function options_key(): string {
		return InformationArchitecture::OPTIONS_KEY;
	}

	/**
	 * @inheritDoc
	 */
	public static function show_toggle_production(): bool {
		return true;
	}
}
