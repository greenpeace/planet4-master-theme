<?php

namespace P4\MasterTheme;

/**
 * Class RegistrationHandler
 */
class RegistrationHandler
{
    /**
     * Constructor.
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
        add_filter('wp_new_user_notification_email_admin', [$this, 'add_site_url_to_admin_notification_email'], 10, 3);

        $features = get_option('planet4_features', []);
        $enforce_sso = !empty($features['enforce_sso']);

        if (!$enforce_sso) {
            return;
        }

        add_filter('wp_new_user_notification_email', [
            $this,
            'hide_password_reset_on_new_user_notification_email',
        ], 10, 3);
        add_action('admin_enqueue_scripts', [$this, 'enqueue_registration_form_styles']);
        // Hide password fields from user profile edit screen.
        add_filter('show_password_fields', '__return_false');
    }

    /**
    * Add site URL to admin notification email.
    *
    * @param array $email User email data.
    * @param WP_User $user User object.
    * @param string $blogname Site name.
    *
    * @return array Modified email data.
    * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
    */
    public function add_site_url_to_admin_notification_email(array $email, \WP_User $user, string $blogname): array
    {
        // Add site URL to admin notification email.
        $site_url = home_url();

        $email['message'] .= "\n";
        $email['message'] .= "URL: {$site_url}\n";

        return $email;
    }

    /**
     * Enqueue stylesheet to hide the password field on the registration form.
     */
    public function enqueue_registration_form_styles(): void
    {
        Loader::enqueue_versioned_style('/admin/css/registration-sso.css');
    }

    /**
     * Hide password reset on new user notification email.
     *
     * @param array $email Email data.
     * @param WP_User $user User object.
     * @param string $blogname Blog name.
     *
     * @return array Modified email data.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function hide_password_reset_on_new_user_notification_email(
        array $email,
        \WP_User $user,
        string $blogname
    ): array {
        $email['message'] = __('Hi', 'planet4-master-theme') . " {$user->user_login},\n\n" .
            __("Your account is ready.\nPlease sign in using", 'planet4-master-theme') . " {$user->user_email}\n\n" .
            wp_login_url();

        return $email;
    }
}
