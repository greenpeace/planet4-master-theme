<?php

namespace P4\MasterTheme;

/**
 * Class DashboardNotice
 */
class DashboardNotice
{
    /**
     * API endpoint for retrieving the announcements content
     *
     */
    private const ANNOUNCEMENTS_API = 'https://planet4.greenpeace.org/wp-json/planet4/v1/announcements/';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_notices', [$this, 'show_dashboard_notice']);
    }

    /**
     * Show P4 Announcements notice on dashboard.
     */
    public function show_dashboard_notice(): void
    {
        // Show only on dashboard.
        $screen = get_current_screen();
        if (null === $screen || 'dashboard' !== $screen->id) {
            return;
        }

        // Don't show an empty message.
        $message = trim($this->retrieve_message());
        if (empty($message)) {
            return;
        }

        echo '<div id="p4-notice" class="notice notice-info">' . wp_kses_post($message) . '</div>';
    }

    /**
     * Get message from cache or API.
     */
    private function retrieve_message(): string
    {
        $content = wp_cache_get('p4-announcements', 'p4-cache-dashboard-notice');

        if ($content === false) {
            $content = $this->fetch_announcements();
            wp_cache_set(
                'p4-announcements',
                $content,
                'p4-cache-dashboard-notice',
                86400
            );
        }

        return $content;
    }

    /**
     * Fetch announcements from the Handbook API.
     */
    private function fetch_announcements(): string
    {
        $response = wp_remote_get(self::ANNOUNCEMENTS_API, [
            'timeout' => 5,
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        if (is_wp_error($response)) {
            if (function_exists('\Sentry\captureException')) {
                \Sentry\captureException($response);
            }
            return '';
        }

        $body = wp_remote_retrieve_body($response);
        $data = json_decode($body, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data) || empty($data['content'])) {
            if (function_exists('\Sentry\captureMessage')) {
                \Sentry\captureMessage('Failed to decode announcements API response' . date("Y-m-d H:i:s"));
            }
            return '';
        }

        return $data['content'] ?? '';
    }
}
