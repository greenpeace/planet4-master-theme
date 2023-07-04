<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class MediaArchive extends Feature
{
    public const OPTIONS_KEY = 'p4ml_main_settings';

    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'feature_media_archive';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __(
            'New Media Archive (Beta)',
            'planet4-master-theme-backend'
        );
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            'Drop-in replacement for the GPI Media Library plugin. If enabled, the plugin can be disabled.',
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

     /**
     * Get the Media Library options settings.
     *
     * @return array Settings for the Media Library page.
     */
    public static function get_options_page(): array
    {
        return [
            'title' => 'Media Archive',
            // phpcs:disable Generic.Files.LineLength.MaxExceeded
            'description' => __('Please enter your Image Archive username and password. Please note that you will need to ask from the Image Archive administrators to enable API access for your account.', 'planet4-master-theme-backend'),
            // phpcs:enable Generic.Files.LineLength.MaxExceeded
            'root_option' => self::OPTIONS_KEY,
            'fields' => [
                [
                    'name' => __('Username', 'planet4-master-theme-backend'),
                    'id' => 'p4ml_api_username',
                    'type' => 'text',
                    'attributes' => [
                        'type' => 'text',
                    ],
                ],
                [
                    'name' => __('Password', 'planet4-master-theme-backend'),
                    'id' => 'p4ml_api_password',
                    'type' => 'text',
                    'attributes' => [
                        'type' => 'password',
                    ],
                ],
            ],
        ];
    }
}
