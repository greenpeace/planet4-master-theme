<?php

namespace P4\MasterTheme;

/**
 * Class Cookies
 */
class Cookies
{
    /**
     * Cookies constructor.
     */
    public function __construct()
    {
        add_filter('gal_set_login_cookie', [ $this, 'filter_google_login_set_login_cookie' ], 10, 1);
    }

    /**
     * Filter setting google login cookie.
     *
     * @param bool $dosetcookie Whether to set the cookie or not.
     *
     * @since 1.9
     *
     */
    public function filter_google_login_set_login_cookie(bool $dosetcookie): bool
    {
        global $pagenow;

        return 'wp-login.php' === $pagenow;
    }
}
