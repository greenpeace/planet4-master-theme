<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class RedirectRedirectPages extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'redirect_redirect_pages';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Redirect Tags redirect pages', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Do a HTTP Redirect from Tags redirect pages to the tag URL, to avoid duplicate content.',
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
