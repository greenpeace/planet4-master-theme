<?php

namespace P4\MasterTheme;

/**
 * Class TwigScriptsEnqueuer.
 *
 * This class is used to enqueue scripts in the Twig templates.
 */
class TwigScriptsEnqueuer
{
    /**
     * TwigScriptsEnqueuer constructor.
     */
    public function __construct()
    {
        $scripts = [
            [
                'action_name' => 'enqueue_share_butttons_script',
                'handle' => 'share-buttons-script',
                'path' => '/assets/build/shareButtons.js',
            ],
            [
                'action_name' => 'enqueue_toggle_comment_submit_script',
                'handle' => 'toggle-comment-submit-script',
                'path' => '/assets/build/toggleCommentSubmit.js',
            ],
            [
                'action_name' => 'enqueue_hubspot_cookie_script',
                'handle' => 'hubspot-cookie-script',
                'path' => '/assets/build/hubspotCookie.js',
            ],
            [
                'handle' => 'google-tag-manager-script',
                'path' => '/assets/build/googleTagManager.js',
                'deps' => [],
                'in_footer' => true,
            ]
        ];

        // Loop through the scripts array and enqueue them
        foreach ($scripts as $script) {
            add_action($script['action_name'], function () use ($script): void {
                $this->enqueue_script(
                    $script['handle'],
                    $script['path'],
                    [],
                    $this->get_file_version($script['path']),
                    true
                );
            });
        }

        add_action('pass_gtm_data', [$this, 'pass_google_tag_manager_data']);
    }

    /**
     * Enqueue a script with the given parameters.
     *
     * @param string $handle The script handle.
     * @param string $path   The script path relative to the theme directory.
     * @param array $deps    (Optional) An array of dependencies.
     * @param string|null $version (Optional) The script version.
     * @param bool $in_footer (Optional) Whether to load the script in the footer.
     */
    private function enqueue_script(
        string $handle,
        string $path,
        array $deps = [],
        ?string $version = null,
        bool $in_footer = true
    ): void {
        wp_enqueue_script(
            $handle,
            get_template_directory_uri() . $path,
            $deps,
            $version,
            $in_footer
        );
    }

    /**
     * Get the file version based on its last modification time.
     *
     * @param string $relative_path The file path relative to the theme directory.
     * @return string|null The version string (timestamp) or null if the file does not exist.
     */
    private function get_file_version(string $relative_path): ?string
    {
        $absolute_path = get_template_directory() . $relative_path;

        if (file_exists($absolute_path)) {
            return filemtime($absolute_path);
        }

        return null;
    }

    public function pass_google_tag_manager_data($context)
    {
        $script = [
            'action_name' => 'enqueue_google_tag_manager_script',
            'handle' => 'google-tag-manager-script',
            'path' => '/assets/build/googleTagManager.js',
        ];

        $this->enqueue_script(
            $script['handle'],
            $script['path'],
            [],
            $this->get_file_version($script['path']),
            true
        );

        if (!wp_script_is('google-tag-manager-script', 'enqueued')) {
            return;
        }

        // Pass the data to the script
        wp_localize_script('google-tag-manager-script', 'googleTagManagerData', $context);
    }
}
