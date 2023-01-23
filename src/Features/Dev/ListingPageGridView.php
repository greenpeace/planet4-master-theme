<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class ListingPageGridView extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'listing_page_grid_view';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Listing page grid view', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Display the list of posts as a grid view on paginated listing page',
            'planet4-master-theme-backend'
        );
    }
}
