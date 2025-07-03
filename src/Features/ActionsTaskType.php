<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ActionsTaskType extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'actions_task_type';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Actions Task Type', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Adds option for showing whether an Action is either Online or IRL on Actions List block',
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
