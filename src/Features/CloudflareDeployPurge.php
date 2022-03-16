<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class CloudflareDeployPurge extends Feature {

	/**
	 * @inheritDoc
	 */
	public static function id(): string {
		return 'cloudflare_deploy_purge';
	}

	/**
	 * @inheritDoc
	 */
	protected static function name(): string {
		return __( 'Purge HTML from Cloudflare on deploy', 'planet4-master-theme-backend' );
	}

	/**
	 * @inheritDoc
	 */
	protected static function description(): string {
		return __(
			'Whether to purge all pages from Cloudflare cache when changing features.<br>Only enable on production, on test instances it results in too many purge requests.',
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
