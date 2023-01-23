<?php

namespace P4\MasterTheme\ImageArchive;

use P4\MasterTheme\Capability;
use P4\MasterTheme\Exception\RemoteCallFailed;
use P4\MasterTheme\Features\ImageArchive;
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
    private static function hooks()
    {
        if (! ImageArchive::is_active()) {
            return;
        }
        add_action('admin_menu', [ self::class, 'picker_page' ], 10);
        add_action('admin_menu', [ self::class, 'media_api_info_page' ], 20);
    }

    /**
     * Register js and output picker root element.
     */
    public static function output_picker(): void
    {
        Loader::enqueue_versioned_style('/admin/css/picker.css');
        Loader::enqueue_versioned_script(
            '/assets/build/archive_picker.js',
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
        if (! current_user_can(Capability::USE_IMAGE_ARCHIVE_PICKER)) {
            return;
        }

        add_menu_page(
            __('Image Archive', 'planet4-master-theme-backend'),
            __('Image Archive', 'planet4-master-theme-backend'),
            Capability::USE_IMAGE_ARCHIVE_PICKER,
            'gpi-image-picker',
            [ self::class, 'output_picker' ],
            'dashicons-format-image',
            11
        );

        add_submenu_page(
            'gpi-image-picker',
            __('Image Archive', 'planet4-master-theme-backend'),
            __('Library', 'planet4-master-theme-backend'),
            Capability::USE_IMAGE_ARCHIVE_PICKER,
            'gpi-image-picker'
        );
    }

    /**
     * Create a page that displays the media api info.
     */
    public static function media_api_info_page(): void
    {
        add_submenu_page(
            'gpi-image-picker',
            __('API info', 'planet4-master-theme-backend'),
            __('API info', 'planet4-master-theme-backend'),
            Capability::USE_IMAGE_ARCHIVE_PICKER,
            'media-api-info',
            [ self::class, 'api_info' ]
        );
    }

    /**
     * Display information about the media API.
     */
    public static function api_info(): void
    {
        Loader::enqueue_versioned_style('/admin/css/media-api-info.css');

		//phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
        try {
            $client = ApiClient::from_cache_or_credentials();
            $fields = $client->show_fields();
            $criteria = $client->show_criteria();
        } catch (RemoteCallFailed $exception) {
            echo 'Failed calling API. Error: ' . $exception;

            return;
        }

        echo '<h1>FIELDS</h1>';
        echo '<p>The fields that can be requested from the API.</p>';
        echo '<dl>';
        foreach ($fields as $id => $description) {
            echo "<dt><h3>{$id}</h3></dt>";
            echo "<dd>{$description}</dd>";
        }
        echo '</dl>';

        echo '<br><hr style="height: 5px;"><br>';

        echo '<h1>CRITERIA</h1>';
        echo '<p>The criteria which can be used when querying the API.</p>';
        echo '<dl>';
        foreach ($criteria as $criterium) {
            $description = nl2br($criterium['Description'] ?? '');
            $examples = preg_replace('/\s*<\s*/', ' < ', $criterium['Examples'] ?? '');
            $examples = preg_replace('/\s*>\s*/', ' > ', $examples);
            echo "<dt><h3>{$criterium['Name']}</h3></dt>";
            echo '<dd>';
            echo $description;
            if ($examples) {
                echo "<pre>{$examples}</pre>";
            }
            echo '</dd>';
        }
        echo '</dl>';
		//phpcs:enable WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
