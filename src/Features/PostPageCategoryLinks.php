<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;
use P4\MasterTheme\Settings\InformationArchitecture;

/**
 * @see description()
 */
class PostPageCategoryLinks extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'post_page_category_links';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Category links on Posts', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'On Posts, link to the catagories, instead of issue pages. (<a href="https://jira.greenpeace.org/browse/PLANET-6747" target="_blank">details</a>)',
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
