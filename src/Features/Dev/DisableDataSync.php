<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class DisableDataSync extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'disable_data_sync';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Disable automatic data sync', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'This will prevent the automated monthly data sync from production to take place.',
            'planet4-master-theme-backend'
        );
    }
}
