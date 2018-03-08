### Run phpunit tests locally

1. Install phpunit 6.x
    ```
    cd /theme/root/folder
    wget -O phpunit https://phar.phpunit.de/phpunit-6.phar
    chmod +x phpunit
    ```
1. Set environment variables $WP_TESTS_DIR, $WP_CORE_DIR. Wordpress core, core-tests and master theme will be copied over to the specified path.
    ```
    export WP_TESTS_DIR="/var/www/html/testing/core-tests"
    export WP_CORE_DIR="/var/www/html/testing/core"
    ```
1. Run install-wp-tests script passing database variables.
    da
    ```
    $ cd /theme/root/folder
    $ bin/install-wp-tests <db-name> <db-user> <db-pass> [db-host]
    $ bin/install-wp-tests planet4_test planet4 planet4 localhost
    ```
1. Run phpunit
    ```
    $ phpunit
    ```
