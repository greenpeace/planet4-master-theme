<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class BetaBlocks extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'beta_blocks';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Beta blocks', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Enable early or unstable versions of blocks in the editor (grouped under "Planet 4 Blocks - BETA" category).',
            'planet4-master-theme-backend'
        );
    }
}
