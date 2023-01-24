<?php

namespace P4\MasterTheme\Commands;

use WP_CLI;

/**
 * Class PostUpdate
 *
 * @example wp p4-post fix-orphans
 */
class FixOrphans extends Command
{
    /**
     * The name to access the command.
     *
     * @return string The command name.
     */
    protected static function get_name(): string
    {
        return 'p4-post fix-orphans';
    }

    /**
     * The description shown in the argument's help.
     *
     * @return string The description text.
     */
    protected static function get_short_description(): string
    {
        return 'Assign admin user to orphan objects';
    }

    /**
     * Update wp_posts data
     *
     * @param array|null $args Positional arguments.
     * @param array|null $assoc_args Named arguments.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(?array $args, ?array $assoc_args): void
    {
        global $wpdb;

        $admin = get_users([ 'role' => 'administrator' ])[0];
        if (! $admin) {
            WP_CLI::error('No admin user found.');
        }

        WP_CLI::log('Assigning orphans to admin user ' . $admin->ID . '.');

        $wpdb->query(
            $wpdb->prepare(
                'UPDATE wp_posts SET post_author=%d WHERE
				post_type in ("post", "page", "campaign", "attachment")
				AND post_author NOT IN ( SELECT ID from wp_users )',
                $admin->ID
            )
        );

        WP_CLI::success('Job done, ' . $wpdb->rows_affected . ' objects were updated.');
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
