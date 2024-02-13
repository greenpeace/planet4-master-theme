<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class CloudflareDeployPurge extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'cloudflare_deploy_purge';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Purge Cloudflare HTML cache on deploy', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Purges all pages from Cloudflare cache on deploy.<br>Only enable on production (in consultation with P4 team).',
            'planet4-master-theme-backend'
        );
    }

    /**
     * @inheritDoc
     */
    public static function show_toggle_production(): bool
    {
        return true;
    }
}
