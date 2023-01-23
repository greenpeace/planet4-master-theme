<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\Comments;

/**
 * @see description().
 */
class GdprCheckbox extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'gdpr_checkbox';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Comments opt-in checkbox', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Display a mandatory opt-in checkbox in the Comments form (GDPR requirement).',
            'planet4-master-theme-backend'
        );
    }

    /**
     * @inheritDoc
     */
    protected static function options_key(): string
    {
        return Comments::OPTIONS_KEY;
    }

    /**
     * @inheritDoc
     */
    public static function show_toggle_production(): bool
    {
        return true;
    }
}
