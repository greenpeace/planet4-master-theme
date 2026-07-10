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
        add_action('user_profile_update_errors', [$this, 'validate_password_policy'], 10, 3);
        add_action('validate_password_reset', [$this, 'validate_password_reset'], 10, 2);

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
     * Validates password on user creation/update.
     *
     * @param WP_Error $errors Error object.
     * @param bool $update True/False if user is being updated.
     * @param \stdClass $user User object.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function validate_password_policy(\WP_Error $errors, bool $update, \stdClass $user): void
    {
        if (!isset($_POST['pass1']) || empty($_POST['pass1'])) {
            return;
        }

        $check = $this->password_policy_check($_POST['pass1']);
        if ($check === true) {
            return;
        }

        $errors->add('pass', $check);
    }

    /**
     * Validates password on password reset.
     *
     * @param WP_Error $errors Error object if any.
     * @param WP_User|WP_Error $user User object.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter
     */
    public function validate_password_reset(\WP_Error $errors, $user): void
    {
        if (!isset($_POST['pass1']) || empty($_POST['pass1'])) {
            return;
        }

        $check = $this->password_policy_check($_POST['pass1']);
        if ($check === true) {
            return;
        }

        $errors->add('pass', $check);
    }

    /**
     * Password validation rules.
     *
     * @param string $password Passwrod value to validate
     */
    private function password_policy_check(string $password): string|bool
    {
        if (empty($password)) {
            return __('Password cannot be empty.', 'planet4-master-theme-backend');
        }

        $length = strlen($password);

        if ($length < 10) {
            return __('Password must be at least 10 characters long.', 'planet4-master-theme-backend');
        }

        if ($length < 15) {
            $errors = [];

            if (!preg_match('/[A-Z]/', $password)) {
                $errors[] = __('at least one uppercase letter', 'planet4-master-theme-backend');
            }
            if (!preg_match('/[0-9]/', $password)) {
                $errors[] = __('at least one number', 'planet4-master-theme-backend');
            }

            if (!empty($errors)) {
                $requirements = implode(' and ', $errors);

                $error_message = sprintf(
                    /* translators: %s is a list of password requirements, e.g. "an uppercase letter and a number" */
                    __('Password must contain %s if it is less than 15 characters.', 'planet4-master-theme-backend'),
                    $requirements
                );

                return $error_message;
            }
        }

        return true;
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
