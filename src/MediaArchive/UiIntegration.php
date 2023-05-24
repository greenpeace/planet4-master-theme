<?php

namespace P4\MasterTheme\MediaArchive;

use P4\MasterTheme\Capability;
use P4\MasterTheme\Features\MediaArchive;
use P4\MasterTheme\Loader;

/**
 * Add some WordPress UI elements if the feature is active.
 */
class UiIntegration
{
    /**
     * UiIntegration constructor.
     */
    public function __construct()
    {
        self::hooks();
    }

    /**
     * Hook up to WordPress.
     */
    private static function hooks(): void
    {
        if (! MediaArchive::is_active()) {
            return;
        }
        add_action('admin_menu', [ self::class, 'picker_page' ], 10);
    }

    /**
     * Register js and output picker root element.
     */
    public static function output_picker(): void
    {
        Loader::enqueue_versioned_style('/admin/css/picker.css');
        Loader::enqueue_versioned_script(
            '/assets/build/media_archive.js',
            [
                'wp-element',
                'wp-compose',
                'wp-components',
                'wp-url',
                'wp-api-fetch',
            ]
        );
        echo '<div id="archive-picker-root"></div>';
    }

    /**
     * Create a page with only the picker.
     */
    public static function picker_page(): void
    {
        if (! current_user_can(Capability::USE_MEDIA_ARCHIVE)) {
            return;
        }

        add_menu_page(
            __('Media Archive', 'planet4-master-theme-backend'),
            __('Media Archive', 'planet4-master-theme-backend'),
            Capability::USE_MEDIA_ARCHIVE,
            'media-picker',
            [ self::class, 'output_picker' ],
            'dashicons-format-image',
            11
        );

        add_submenu_page(
            'media-picker',
            __('Media Archive', 'planet4-master-theme-backend'),
            __('Library', 'planet4-master-theme-backend'),
            Capability::USE_MEDIA_ARCHIVE,
            'media-picker'
        );
    }
}
