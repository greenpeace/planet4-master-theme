<?php

namespace P4\MasterTheme;

use WP_Error;
use WP_User;

/**
 * Class LoginHandler
 */
class LoginHandler
{
    /**
     * Variable that lets us know if the user has or hasn't used google to log in
     *
     */
    protected bool $google_login_error = false;

    /**
     * Constructor.
     */
    public function __construct()
    {
        /**
         * Start output buffering early in the login process
         * to help removing the "Remember Me" checkbox from the WordPress login screen.
         */
        add_action('login_init', function (): void {
            ob_start();
        });

        /**
         * Filter the rendered login page HTML before sending it to the browser.
         * Uses regex to strip out the "Remember Me" checkbox markup.
         */
        add_action('login_footer', function (): void {
            $html = ob_get_clean();

            $features = get_option('planet4_features', []);
            $enforce_sso = !empty($features['enforce_sso']);

            // Clean up the HTML by removing the "Remember Me" checkbox
            $html = preg_replace('/<p[^>]*class=["\']forgetmenot["\'][^>]*>.*?<\/p>/is', '', $html);

            if ($enforce_sso) {
                if (isset($_GET['loggedout']) && $_GET['loggedout'] === 'true') {
                    wp_redirect(home_url());
                    exit;
                }

                $gal_instance = google_apps_login();
                if (!method_exists($gal_instance, 'ga_start_auth_get_url')) {
                    return;
                }

                $ga_url = $gal_instance->ga_start_auth_get_url();

                if (!empty($ga_url)) {
                    wp_redirect(esc_url_raw($ga_url));
                    exit;
                }
            }

            echo $html;
        });

        /**
         * Disable the "Remember Me" functionality server-side.
         * Ensures that even if someone manipulates the form, the field won't persist sessions.
         */
        add_action('init', function (): void {
            if (empty($_POST['log']) || empty($_POST['pwd'])) {
                return;
            }

            unset($_POST['rememberme']);
        }, 1);

        add_action('init', [$this, 'login_redirect'], 1);
        add_filter('authenticate', [$this, 'enforce_google_signon'], 4, 3);
        add_filter('authenticate', [$this, 'check_google_login_error'], 30, 1);
        add_filter('authenticate', [$this, 'custom_block_login_if_rate_limited'], 30, 3);
        add_filter('login_headerurl', [$this, 'add_login_logo_url']);
        add_filter('login_headertext', [$this, 'add_login_logo_url_title']);
        add_action('login_enqueue_scripts', [$this, 'add_login_stylesheet']);
        add_action('wp_login_failed', [$this, 'custom_track_failed_login']);
        add_action('wp_login', [$this, 'remove_blocked_login'], 10, 2);
        add_action('user_profile_update_errors', [$this, 'validate_password_policy'], 10, 3);
        add_action('validate_password_reset', [$this, 'validate_password_reset'], 10, 2);
    }

    /**
     * Removes blocked login functionality after 15 minutes.
     *
     * @param string $user_login The username of the user.
     * @param string $user       The current user logging in.
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function remove_blocked_login(string $user_login, WP_User $user): void
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        delete_transient('failed_login_' . md5($ip)); //NOSONAR
        delete_transient('blocked_' . md5($ip)); //NOSONAR
    }

    /**
     * Tracks how many attempts failed at login for a specific username.
     *
     * @param string $username The username of the user.
     */
    // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
    public function custom_track_failed_login(string $username): void
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        $key = 'failed_login_' . md5($ip); //NOSONAR

        $attempts = get_transient($key);
        if (!$attempts) {
            $attempts = 0;
        }

        $attempts++;
        set_transient($key, $attempts, 600); // store for 10 minutes

        if ($attempts < 5) {
            return;
        }
        // block for 15 minutes
        set_transient('blocked_' . md5($ip), true, 900); //NOSONAR
    }

     /**
     * Forces a timeout before trying to login again after a certain amount of failed attempts.
     *
     * @param WP_User|WP_Error $user The current user logging in.
     * @param string           $username The username of the user.
     * @param string           $password The password of the user.
     */
    public function custom_block_login_if_rate_limited(
        WP_User|WP_error $user,
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
        string $username,
        // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
        string $password
    ): mixed {
        $ip = $_SERVER['REMOTE_ADDR'];

        if (get_transient('blocked_' . md5($ip))) { //NOSONAR
            return new WP_Error(
                'too_many_attempts',
                __('Too many failed login attempts. Please try again in 15 minutes.', 'planet4-master-theme-backend')
            );
        }

        if (is_wp_error($user)) {
            return new WP_Error(
                'custom-login-error',
                __('Invalid username or password', 'planet4-master-theme'),
            );
        }

        return $user;
    }

    /**
     * Detects and redirects login from non-canonical domain to preferred domain
     */
    public function login_redirect(): void
    {
        if (!isset($GLOBALS['pagenow']) || 'wp-login.php' !== $GLOBALS['pagenow']) {
            // Not on the login page, as you were.
            return;
        }

        if (!isset($_SERVER['HTTP_HOST']) || !isset($_SERVER['SERVER_NAME'])) {
            // If either of these are unset, we can't be sure we want to redirect.
            return;
        }

        if ($_SERVER['HTTP_HOST'] === $_SERVER['SERVER_NAME']) {
            return;
        }

        $adminUrl = str_replace(
            sanitize_text_field(wp_unslash($_SERVER['HTTP_HOST'])),
            sanitize_text_field(wp_unslash($_SERVER['SERVER_NAME'])),
            get_admin_url()
        );
        if (wp_safe_redirect($adminUrl)) {
            exit;
        }
    }

    /**
     * Forces a user to login using Google Auth if they have a greenpeace.org email
     *
     * @param WP_User|WP_Error|null $user The current user logging in.
     * @param String|null $username The username of the user.
     * @param String|null $password The password of the user.
     * @return WP_User|WP_Error|null
     */
    public function enforce_google_signon($user, ?string $username = null, ?string $password = null)
    {

        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            return $user;
        }

        if (empty($username) || empty($password)) {
            return $user;
        }

        if (strpos($username, '@')) {
            $user_data = get_user_by('email', trim(wp_unslash($username)));
        } else {
            $login = trim($username);
            $user_data = get_user_by('login', $login);
        }

        if (empty($user_data) || is_wp_error($user)) {
            return $user;
        }

        $email_user_name = mb_substr($user_data->data->user_email, 0, strpos($user_data->data->user_email, '@'));

        // Dont enforce google login on aliases.
        if (strpos($email_user_name, '+')) {
            return $user;
        }

        $domain = '@greenpeace.org';
        if (mb_substr($user_data->data->user_email, -strlen($domain)) === $domain) {
            $this->google_login_error = true;
        }

        return $user;
    }

    /**
     * Checks if we have set a google login error earlier on so we can prevent login if google login wasn't used
     *
     * @param WP_User|WP_Error|null $user The current user logging in.
     *
     * @return WP_User|WP_Error|null
     */
    public function check_google_login_error($user)
    {
        if ($this->google_login_error) {
            $this->google_login_error = false;
            return new WP_Error(
                'google_login',
                __(
                    'You are trying to login with a Greenpeace email. Please use the Google login button instead.',
                    'planet4-master-theme-backend'
                )
            );
        }

        return $user;
    }

    /**
     * Sets the URL for the logo link in the login page.
     */
    public function add_login_logo_url(): string
    {
        return home_url();
    }

    /**
     * Sets the title for the logo link in the login page.
     */
    public function add_login_logo_url_title(): string
    {
        return get_bloginfo('name');
    }

    /**
     * Sets a custom stylesheet for the login page.
     */
    public function add_login_stylesheet(): void
    {
        wp_enqueue_style(
            'custom-login',
            get_template_directory_uri() . '/admin/css/login.css',
            [],
            Loader::theme_file_ver('admin/css/login.css')
        );
    }

     // @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
    /**
     * Validates password on user creation/update.
     *
     * @param WP_Error   $errors Error object.
     * @param bool       $update True/False if user is being updated.
     * @param \stdClass    $user   User object.
     */
    public function validate_password_policy(WP_Error $errors, bool $update, \stdClass $user): void
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
     * @param WP_Error         $errors Error object if any.
     * @param WP_User|WP_Error $user   User object.
     */
    public function validate_password_reset(WP_Error $errors, $user): void
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
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter

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
}
