<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class AllowAllBlocks extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'allow_all_blocks';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('All blocks', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Enable all blocks in the editor for all post types.',
            'planet4-master-theme-backend'
        );
    }
}
