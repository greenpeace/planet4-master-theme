<?php

namespace P4\MasterTheme;

/**
 * Handles notifications in the dashboard and other admin areas.
 */
class AdminNotificationsManager
{
    /**
     * Key of notice seen by user
     */
    private const DASHBOARD_MESSAGE_KEY = 'last_p4_notice';

    /**
     * Version of notice
     */
    private const DASHBOARD_MESSAGE_VERSION = '0.4';

    public function __construct()
    {
        add_action('admin_notices', [$this, 'show_dashboard_notice']);
        add_action('wp_ajax_dismiss_dashboard_notice', [$this, 'dismiss_dashboard_notice']);
        add_action('admin_enqueue_scripts', [$this, 'add_all_posts_notification'], 100);
    }

    /**
     * Add a notification to the editor of the page used to show all posts.
     */
    public function add_all_posts_notification(): void {
        wp_add_inline_script(
            'wp-notices',
            sprintf(
                'wp.data.dispatch( "core/notices" ).createNotice("warning", "%s" , { isDismissible: false, actions: [ { label: "%s", url: "options-reading.php"} ] } )',
                __('The content on this page is hidden because this page is being used as your \"All Posts\" listing page. You can disable this by un-setting the \"Posts page\"', 'planet4-master-theme'),
                __('here', 'planet4-master-theme')
            )
        );
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
        return '<h2>Welcome to the new P4 message board!</h2>
            <p>New to the Planet 4 platform? Here are some self - paced courses that can help you get up to speed. 👇
                <ul>
                    <li><span style="margin-right: 3px;">
                        <a href="https://greenpeace.studytube.nl/courses/22122">Planet 4 Fundamentals</a> 🌏</span>
                        learn the very basic of Planet 4.</li>
                    <li><span style="margin-right: 3px;">
                        <a href="https://greenpeace.studytube.nl/courses/23208/planet-4">Planet 4 Power User</a> 🚀</span>
                        a more in-depth course to understand how to manage a P4 website and how to make best use of its engagement features.</li>
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
