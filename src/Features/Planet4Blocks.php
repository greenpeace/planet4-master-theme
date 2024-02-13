<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class Planet4Blocks extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'planet4_blocks';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Planet 4 Blocks', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Activates all <a href="https://planet4.greenpeace.org/content/blocks/" target="_blank">Planet 4 blocks</a>',
            'planet4-master-theme-backend'
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
