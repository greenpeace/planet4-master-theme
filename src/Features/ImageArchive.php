<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ImageArchive extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'feature_image_archive';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __(
			'New Image Archive (Beta)',
			'planet4-master-theme-backend'
		);
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Beta test the new Image Archive. This will replace the GPI Media Library plugin.',
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
