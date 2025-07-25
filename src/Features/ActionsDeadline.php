<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ActionsDeadline extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'actions_deadline';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Actions Deadline', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Adds option for showing a countdown on Actions List block',
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
