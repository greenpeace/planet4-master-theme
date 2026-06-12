<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class MandatoryImageAltText extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'mandatory_image_alt_text';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Enforce images alt-text', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'All images included in a Post would require an alt-text before publishing.',
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
