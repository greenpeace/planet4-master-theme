<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class EnforceSingleSignOn extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'enforce_sso';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Enforce Single Sign On', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Enable Google SSO',
            'planet4-master-theme-backend'
        );
    }
}
