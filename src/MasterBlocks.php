<?php

namespace P4\MasterTheme;

/**
 * Class MasterBlocks
 * The main class that handles Planet4 blocks.
 */
class MasterBlocks
{
    /**
     * MasterBlocks constructor.
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Class hooks.
     */
    private function hooks(): void
    {
        // Register "planet4-blocks" block category.
        add_filter('block_categories_all', function ($categories) {

            // Adding a new category.
            array_unshift($categories, [
                'slug' => 'planet4-blocks',
                'title' => 'Planet 4 Blocks',
            ]);

            return $categories;
        });

        // Admin scripts.
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_block_editor_script' ]);
        // Frontend scripts.
        add_action('wp_enqueue_scripts', [ $this, 'enqueue_block_public_assets' ]);
    }

    /**
     * Enqueue block editor script.
     */
    public function enqueue_block_editor_script(): void
    {
        $theme_dir = get_template_directory_uri();
        // Enqueue editor script for all Blocks in this Plugin.
        wp_enqueue_script(
            'planet4-blocks-theme-editor-script',
            $theme_dir . '/assets/build/editorIndex.js',
            [
                'wp-blocks', // Helpers for registering blocks.
                'wp-components', // Wordpress components.
                'wp-element', // WP React wrapper.
                'wp-data', // WP data helpers.
                'wp-i18n', // Exports the __() function.
                'wp-editor',
                'wp-edit-post',
            ]
        );

        $reflection_vars = self::reflect_js_variables();
        wp_localize_script('planet4-blocks-theme-editor-script', 'p4_vars', $reflection_vars);
    }

    /**
     * Load block assets for the frontend.
     */
    public function enqueue_block_public_assets(): void
    {
        $theme_dir = get_template_directory_uri();

        $js_creation = filectime(get_template_directory() . '/assets/build/frontendIndex.js');
        // Include React in the Frontend.
        wp_register_script(
            'planet4-blocks-theme-script',
            $theme_dir . '/assets/build/frontendIndex.js',
            [
                // WP React wrapper.
                'wp-element',
                // Exports the __() function.
                'wp-i18n',
            ],
            $js_creation,
            true
        );
        wp_enqueue_script('planet4-blocks-theme-script');

        $reflection_vars = self::reflect_js_variables();
        wp_localize_script('planet4-blocks-theme-script', 'p4_vars', $reflection_vars);
    }

    /**
     * Get Planet 4 options
     *
     * @return array
     */
    private function get_p4_options(): array
    {
        $option_values = get_option('planet4_options');

        $cookies_default_copy = [
            'necessary_cookies_name' => $option_values['necessary_cookies_name'] ?? '',
            'necessary_cookies_description' => $option_values['necessary_cookies_description'] ?? '',
            'analytical_cookies_name' => $option_values['analytical_cookies_name'] ?? '',
            'analytical_cookies_description' => $option_values['analytical_cookies_description'] ?? '',
            'all_cookies_name' => $option_values['all_cookies_name'] ?? '',
            'all_cookies_description' => $option_values['all_cookies_description'] ?? '',
        ];

        return [
            'enable_analytical_cookies' => $option_values['enable_analytical_cookies'] ?? '',
            'enable_google_consent_mode' => $option_values['enable_google_consent_mode'] ?? '',
            'cookies_default_copy' => $cookies_default_copy,
        ];
    }

    /**
     * Get Planet 4 features
     *
     * @return array
     */
    private function get_p4_features(): array
    {
        return get_option('planet4_features');
    }

    /**
     * Add variables reflected from PHP to JS.
     */
    public function reflect_js_variables(): array
    {
        return [
            'options' => $this->get_p4_options(),
            'features' => $this->get_p4_features(),
        ];
    }
}
