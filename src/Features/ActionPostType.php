<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\InformationArchitecture;

/**
 * @see description().
 */
class ActionPostType extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'action_post_type';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Action post type', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Enable <a href="https://jira.greenpeace.org/browse/PLANET-6515" target="_blank">Action post type</a> and Action Type taxonomy. Actions will also be selectable in Take Action Boxout and Covers blocks.',
            'planet4-master-theme-backend'
        );
    }

    /**
     * @inheritDoc
     */
    protected static function options_key(): string
    {
        return InformationArchitecture::OPTIONS_KEY;
    }

    /**
     * @inheritDoc
     */
    public static function show_toggle_production(): bool
    {
        return true;
    }
}
