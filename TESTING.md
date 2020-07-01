### Run PHPUnit tests locally

Your containers should have everything needed to run tests.  
To follow these instructions, enter `make php-shell`.

1. Install phpunit 6.x  
    `phpunit` is included in `vendor/bin/phpunit`.  
    If it is not, you can install it:
    ```sh
    $ cd /theme/root/folder
    $ wget -O phpunit https://phar.phpunit.de/phpunit-6.phar
    $ chmod +x phpunit
    ```
    If svn is not installed:
    ```sh
    $ apt update && apt install subversion
    ```
1. Set environment variables `$WP_TESTS_DIR`, `$WP_CORE_DIR` for your installation.  
Wordpress core, core-tests and master theme will be copied over to the specified path.
    ```sh
    export WP_TESTS_DIR="/var/www/html/testing/core-tests"
    export WP_CORE_DIR="/var/www/html/testing/core"
    ```
1. Run install-wp-tests script passing database variables.
Note that a new clean database should be provided as the argument, because database will be cleaned each time a test runs.
    ```sh
    $ cd /theme/root/folder
    $ # bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
    $ bin/install-wp-tests.sh planet4_test root root db
    ```
1. Run phpunit
    ```sh
    $ vendor/bin/phpunit
    ```
