<?php

declare(strict_types=1);

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Features\GdprCheckbox;
use P4\MasterTheme\Loader;

/**
 * Settings related to Posts comments.
 */
class Comments
{
    public const OPTIONS_KEY = 'planet4_comments';

    /**
     * Get the features options page settings.
     *
     * @return array Settings for the options page.
     */
    public static function get_options_page(): array
    {
        return [
            'title' => 'Comments',
            'description' => 'Options related to comments.',
            'root_option' => self::OPTIONS_KEY,
            'fields' => self::get_fields(),
            'add_scripts' => static function (): void {
                Loader::enqueue_versioned_script('/admin/js/features_save_redirect.js');
            },
        ];
    }

    /**
     * Get form fields.
     *
     * @return array  The fields.
     */
    public static function get_fields(): array
    {
        return [
            GdprCheckbox::get_cmb_field(),
        ];
    }
}
