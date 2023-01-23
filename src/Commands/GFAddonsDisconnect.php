<?php

namespace P4\MasterTheme\Commands;

use WP_CLI;

/**
 * Class GFAddonsDisconnect
 *
 * Remove existing authentications from GF addons.
 *
 * @example wp p4-gf-addons disconnect
 */
class GFAddonsDisconnect extends Command
{
    /**
     * The name to access the command.
     *
     * @return string The command name.
     */
    protected static function get_name(): string
    {
        return 'p4-gf-addons disconnect';
    }

    /**
     * The description shown in the argument's help.
     *
     * @return string The description text.
     */
    protected static function get_short_description(): string
    {
        return 'Disconnect Gravity Forms addons';
    }

    /**
     * Update wp_posts data
     *
     * @param array|null $args Positional arguments.
     * @param array|null $assoc_args Named arguments.
     */
    public static function execute(?array $args, ?array $assoc_args): void
    {
        WP_CLI::log('Disconnecting GF addons.');

        self::disconnect_hubspot();
        self::disconnect_webapi();

        WP_CLI::success('Job done.');
    }

    /**
     * Disconnect Hubspot addon
     */
    private static function disconnect_hubspot(): void
    {
        $name = 'gravityformsaddon_gravityformshubspot_settings';
        $options = get_option($name, null);

        if (! $options || ! is_array($options['auth_token'])) {
            WP_CLI::log('No Hubspot authentication to handle.');
            return;
        }

        $options['auth_token']['access_token'] = '';
        $options['auth_token']['refresh_token'] = '';

        update_option($name, $options);
        WP_CLI::log('Hubspot settings updated.');
    }

    /**
     * Disconnect Webapi addon
     */
    private static function disconnect_webapi(): void
    {
        $name = 'gravityformsaddon_gravityformswebapi_settings';
        $options = get_option($name, null);

        if (! is_array($options)) {
            WP_CLI::log('No WebApi authentication to handle.');
            return;
        }

        $options['enabled'] = '0';

        update_option($name, $options);
        WP_CLI::log('WebApi settings updated.');
    }
}
