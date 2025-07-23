<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ActionsUserPersonalization extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'actions_user_personalization';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Actions User Personalization', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Shows Actions as completed if the user has visited them already. This feature only works on Actions that use the Resistance Hub template.',
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
