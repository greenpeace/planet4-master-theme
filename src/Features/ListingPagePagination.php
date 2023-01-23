<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\InformationArchitecture;

/**
 * @see description()
 */
class ListingPagePagination extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'listing_page_pagination';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Listing page pagination', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __('Use the new paginated tag, category, author, post & action type listing pages.', 'planet4-master-theme-backend');
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
