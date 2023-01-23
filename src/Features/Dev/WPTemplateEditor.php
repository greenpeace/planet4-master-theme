<?php

namespace P4\MasterTheme\Features\Dev;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class WPTemplateEditor extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'wp_template_editor';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('WordPress template editor', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Enable the WordPress "template editor" to allow Full Site Editiong.',
            'planet4-master-theme-backend'
        );
    }
}
