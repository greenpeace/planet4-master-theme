<?php

namespace P4\MasterTheme;

use WP_Error;

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
            $html = preg_replace('/<p[^>]*class=["\']forgetmenot["\'][^>]*>.*?<\/p>/is', '', $html);
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
        add_filter('login_headerurl', [$this, 'add_login_logo_url']);
        add_filter('login_headertext', [$this, 'add_login_logo_url_title']);
        add_action('login_enqueue_scripts', [$this, 'add_login_stylesheet']);
        add_filter('login_errors', [$this, 'custom_login_error'], 10, 1);
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

    /**
     * Returns new generic error message for login page.
     *
     * @param string $error The original login error message generated by WordPress.
     * @return string Modified error message.
     */
    public function custom_login_error(string $error): string
    {
        $action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';

        if ($action === 'login') {
            return __('Invalid username or password', 'planet4-master-theme');
        }

        return $error;
    }
}
