<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description()
 */
class CoreBlockPatterns extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'core_block_patterns';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Core block patterns', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Enable default block patterns that come with WordPress.',
            'planet4-master-theme-backend'
        );
    }
}
