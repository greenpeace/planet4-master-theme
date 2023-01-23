<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class EngagingNetworks extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'feature_engaging_networks';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Engaging Networks integration', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Activate the EN Form block, as well as the "Progress Bar inside EN Form" Counter block style.',
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
