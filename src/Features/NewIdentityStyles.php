<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\ExperimentalFeatures;

/**
 * @see description().
 */
class NewIdentityStyles extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'new_identity_styles';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('New identity styles', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Enable new Greenpeace visual identity',
            'planet4-master-theme-backend'
        );
    }

    /**
     * @inheritDoc
     */
    protected static function options_key(): string
    {
        return ExperimentalFeatures::OPTIONS_KEY;
    }

    /**
     * @inheritDoc
     */
    public static function show_toggle_production(): bool
    {
        return true;
    }
}
