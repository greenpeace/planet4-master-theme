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
            $categories[] = array(
                'slug' => 'planet4-blocks',
                'title' => 'Planet 4 Blocks',
            );

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
            'planet4-blocks-editor-script',
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
    }

    /**
     * Load block assets for the frontend.
     */
    public function enqueue_block_public_assets(): void
    {
        $theme_dir = get_template_directory_uri();
        // Add master theme's main css as dependency for blocks css.
        wp_enqueue_style(
            'planet4-blocks-style',
            $theme_dir . '/assets/build/blockStyle.min.css',
            [
                'bootstrap',
                'parent-style',
            ],
            Loader::theme_file_ver('assets/build/blockStyle.min.css')
        );

        $js_creation = filectime(get_template_directory() . '/assets/build/frontendIndex.js');
        // Include React in the Frontend.
        wp_register_script(
            'planet4-blocks-script',
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
        wp_enqueue_script('planet4-blocks-script');
    }
}
