<?php

namespace P4\MasterTheme\Commands;

use WP_CLI;

/**
 * Class SaveCloudflareKey
 */
class SaveCloudflareKey extends Command
{
    /**
     * The name to access the command.
     *
     * @return string The command name.
     */
    protected static function get_name(): string
    {
        return 'p4-cf-key-in-db';
    }

    /**
     * The description shown in the argument's help.
     *
     * @return string The description text.
     */
    protected static function get_short_description(): string
    {
        return 'Put Cloudflare key in DB from config file';
    }

    /**
     * The logic of the command. Has WP_CLI command signature.
     *
     * @param array|null $args Positional arguments.
     * @param array|null $assoc_args Named arguments.
     *
     * @throws WP_CLI\ExitException If no hostname or Cloudflare key is not present.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(?array $args, ?array $assoc_args): void
    {
        $hostname = $args[0] ?? null;
        if (empty($hostname)) {
            WP_CLI::error('Please specify the hostname.');
        }

        if (! defined('CLOUDFLARE_API_KEY') || empty(CLOUDFLARE_API_KEY)) {
            WP_CLI::error('CLOUDFLARE_API_KEY constant is not set.');
        }

        $domain_parts = explode('.', $hostname);

        $root_domain = implode('.', array_slice($domain_parts, - 2));
        update_option('cloudflare_api_key', CLOUDFLARE_API_KEY);
        update_option('automatic_platform_optimization', [ 'value' => 1 ]);
        update_option('cloudflare_cached_domain_name', $root_domain);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
