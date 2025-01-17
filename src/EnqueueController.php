<?php

namespace P4\MasterTheme;

/**
 * Class EnqueueController.
 *
 * This class is used to enqueue scripts and styles.
 */
class EnqueueController
{
    /**
     * EnqueueController constructor.
     */
    public function __construct()
    {
        add_action('enqueue_toggle_comment_submit_script', [$this, 'enqueue_toggle_comment_submit']);
        add_action('enqueue_hubspot_cookie_script', [$this, 'enqueue_hubspot_cookie']);
        add_action('enqueue_share_buttons_script', [$this, 'enqueue_share_buttons']);
        add_action('enqueue_google_tag_manager_script', [$this, 'enqueue_google_tag_manager']);
    }

    /**
     * Enqueues the toggle comment submit script.
     *
     * This method registers and enqueues the JavaScript file used to manage
     * the functionality of toggling the comment submit button based on user input.
     *
     */
    public function enqueue_toggle_comment_submit(): void
    {
        $this->enqueue_script(
            'toggle-comment-submit-script',
            '/assets/build/toggleCommentSubmit.js',
            [],
            $this->get_file_version('/assets/build/toggleCommentSubmit.js'),
            true
        );
    }

    /**
     * Enqueues the HubSpot cookie script.
     *
     * This method registers and enqueues the JavaScript file for handling HubSpot
     * cookie management on the website.
     *
     */
    public function enqueue_hubspot_cookie(): void
    {
        $this->enqueue_script(
            'hubspot-cookie-script',
            '/assets/build/hubspotCookie.js',
            [],
            $this->get_file_version('/assets/build/hubspotCookie.js'),
            true
        );
    }

    public function enqueue_google_tag_manager(): void
    {
        $this->enqueue_script(
            'google-tag-manager-script',
            '/assets/build/googleTagManager.js',
            [],
            $this->get_file_version('/assets/build/googleTagManager.js'),
            true
        );

        add_action('pass_gtm_data', [$this, 'pass_google_tag_manager_data']);
    }

    public function pass_google_tag_manager_data()
    {
        if (!wp_script_is('google-tag-manager-script', 'enqueued')) {
            return;
        }

        $context = Timber::context();

        // Pass the data to the script
        wp_localize_script('google-tag-manager-script', 'googleTagManagerData', $context);
    }

    /**
     * Enqueues the share buttons script.
     *
     * This method registers and enqueues the JavaScript file for handling
     * share buttons on the website.
     *
     */
    public function enqueue_share_buttons(array $social_data): void
    {
        $script = [
            'id' => 'shareButtonsData',
            'name' => 'share-buttons-script',
            'path' => '/assets/build/shareButtons.js',
        ];

        $this->enqueue_script(
            $script['name'],
            $script['path'],
            [],
            $this->get_file_version($script['path']),
            true
        );

        if (!wp_script_is($script['name'], 'enqueued')) {
            return;
        }

        $data = [
            'link' => $social_data['link'] ?? null,
            'title' => $social_data['title'] ?? null,
            'description' => $social_data['description'] ?? null,
            'share_url' => $social_data['p4_gf_share_url_override'] ?? null,
            'share_text' => $social_data['p4_gf_share_text_override'] ?? null,
        ];

        wp_localize_script($script['name'], $script['id'], $data);
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
}
