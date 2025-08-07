<?php

namespace P4\MasterTheme;

/**
 * Class DashboardNotice
 */
class DashboardNotice
{
    /**
     * Key of notice seen by user
     *
     */
    private const DASHBOARD_MESSAGE_KEY = 'last_p4_notice';

    /**
     * Version of notice
     *
     */
    private const DASHBOARD_MESSAGE_VERSION = '0.6';

    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('admin_notices', [$this, 'show_dashboard_notice']);
        add_action('wp_ajax_dismiss_dashboard_notice', [$this, 'dismiss_dashboard_notice']);
    }

    /**
     * Show P4 team message on dashboard.
     */
    public function show_dashboard_notice(): void
    {
        // Show only on dashboard.
        $screen = get_current_screen();
        if (null === $screen || 'dashboard' !== $screen->id) {
            return;
        }

        // Don't show a dismissed version.
        $last_notice = get_user_meta(get_current_user_id(), self::DASHBOARD_MESSAGE_KEY, true);
        if (version_compare(self::DASHBOARD_MESSAGE_VERSION, $last_notice, '<=')) {
            return;
        }

        // Don't show an empty message.
        $message = trim($this->p4_message());
        if (empty($message)) {
            return;
        }

        do_action('enqueue_dismiss_dashboard_notice_script');

        echo '<div id="p4-notice" class="notice notice-info is-dismissible">' . wp_kses_post($message) . '</div>';
    }

    /**
     * A message from Planet4 team.
     *
     * Message title should be a <h2> tag.
     * Message text should be written into <p> tags.
     * Return an empty string if no message for this version.
     *
     * Version number DASHBOARD_MESSAGE_VERSION has to be incremented
     * each time we add a new message.
     *
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    private function p4_message(): string
    {
        return '<h2>📢 The new Posts List and Actions List blocks are here!</h2>
            <p>
                <ul>
                    <li><span style="margin-right: 3px;">
                        <a href="https://planet4.greenpeace.org/content/blocks/posts-list/" target="_blank">Posts List</a>:
                        <span> 📝 It replaces the Articles block and the Covers block Content Style</span>
                    </li>
                    <li><span style="margin-right: 3px;">
                        <a href="https://planet4.greenpeace.org/content/blocks/actions-list/">Actions List</a>:
                        <span> ✨ It replaces the Covers block Take Action Style</span>
                    </li>
                </ul>
            </p>';
    }
    // phpcs:enable Generic.Files.LineLength.MaxExceeded

    /**
     * Dismiss P4 notice of dashboard, by saving the last version read in user meta field.
     *
     * @uses wp_die()
     */
    public function dismiss_dashboard_notice(): void
    {
        $user_id = get_current_user_id();
        if (0 === $user_id) {
            wp_die('User not logged in.', 401);
        }

        $res = update_user_meta(
            $user_id,
            self::DASHBOARD_MESSAGE_KEY,
            self::DASHBOARD_MESSAGE_VERSION
        );
        if (false === $res) {
            wp_die('User meta update failed.', 500);
        }

        wp_die('Notice dismissed.', 200);
    }
}
