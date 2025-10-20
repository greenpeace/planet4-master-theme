<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class NewTimelineBlock extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'new_timeline_block';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('New Timeline block', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Switches existing Timeline blocks to the <a href="https://www.figma.com/design/9NtbY8n3at8uOEJTsLrETb/P4-Design-System?node-id=9010-18014&t=9M5kqEc7IljnyUMo-0" target="_blank">new implementation</a>.',
            'planet4-master-theme-backend'
        );
    }
}
