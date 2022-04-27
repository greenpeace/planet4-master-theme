<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class DisableTagRedirectPages extends Feature {
	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'disable_tag_redirect_pages';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Disable Tag Redirect Pages', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Use default auto-generated tag listing page.',
			'planet4-master-theme-backend'
		);
	}
}
