<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ActionOptions extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'action_options';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Action Options', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Adds options for showing whether an Action is either Online or IRL and deadline on Actions List block',
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
