<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * Feature to enforce SSO login.
 * Note: This feature is not exposed in the admin UI (not registered in Features.php).
 * It can be controlled via environment constants or deployment scripts.
 *
 * @see Feature::is_active()
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
            'Enable Google SSO',
            'planet4-master-theme-backend'
        );
    }
}
