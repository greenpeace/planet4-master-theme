<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class PurgeOnFeatureChanges extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'purge_on_feature_changes';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Purge Cloudflare HTML cache on feature changes', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Purges all pages from Cloudflare cache on feature changes.<br>Only enable on production (in consultation with P4 team).',
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
