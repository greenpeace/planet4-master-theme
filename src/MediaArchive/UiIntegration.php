<?php

namespace P4\MasterTheme\MediaArchive;

use P4\MasterTheme\Capability;
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
        add_action('admin_menu', [ self::class, 'picker_page' ], 10);
        add_action('admin_menu', [ self::class, 'ml_credentials_page' ], 10);
    }

    /**
     * Register js and output picker root element.
     */
    public static function output_picker(): void
    {
        Loader::enqueue_versioned_style('/admin/css/archive-picker.css');
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

        add_media_page(
            __('Greenpeace Media', 'planet4-master-theme-backend'),
            __('Greenpeace Media', 'planet4-master-theme-backend'),
            Capability::USE_MEDIA_ARCHIVE,
            'media-picker',
            [ self::class, 'output_picker' ],
        );
    }

    /**
     * Add Media Library credential page to media nav.
     */
    public static function ml_credentials_page(): void
    {
        if (! current_user_can(Capability::USE_MEDIA_ARCHIVE)) {
            return;
        }

        add_media_page(
            __('Greenpeace Media Settings', 'planet4-master-theme-backend'),
            __('Greenpeace Media Settings', 'planet4-master-theme-backend'),
            Capability::USE_MEDIA_ARCHIVE,
            'media-archive-settings',
            function (): void {
                $option_key = 'p4ml_main_settings';
                // phpcs:disable Generic.Files.LineLength.MaxExceeded
                $description = __('Please enter your Greenpeace Media username and password. Please note that you will need to ask from the Greenpeace Media administrators to enable API access for your account.', 'planet4-master-theme-backend');
                // phpcs:enable Generic.Files.LineLength.MaxExceeded
                $form = cmb2_metabox_form(
                    [
                        'id' => 'option_metabox',
                        'show_on' => [
                            'key' => 'options-page',
                            'value' => [
                                $option_key,
                            ],
                        ],
                        'show_names' => true,
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
                    ],
                    $option_key,
                    [ 'echo' => false ]
                );

                echo sprintf(
                    '<div class="wrap %s">
                        <h2>%s</h2>
                        %s
                        %s
                    </div>',
                    esc_attr($option_key),
                    esc_html(get_admin_page_title()),
                    wp_kses($description ? '<div>' . $description . '</div>' : '', 'post'),
                    $form // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                );
            }
        );
    }
}
