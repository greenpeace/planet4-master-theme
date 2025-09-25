<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class CloudflareTurnstile extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'cloudflare_turnstile';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Cloudflare Turnstile', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Additional anti-spam mechanism for comments. It will show a verification option on some users.',
            'planet4-master-theme-backend',
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
