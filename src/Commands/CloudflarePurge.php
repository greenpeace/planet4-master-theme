<?php

namespace P4\MasterTheme\Commands;

use P4\MasterTheme\CloudflarePurger;
use P4\MasterTheme\Features;
use WP_CLI;

/**
 * Class CloudflarePurge
 */
class CloudflarePurge extends Command
{
    /**
     * The name to access the command.
     *
     * @return string The command name.
     */
    protected static function get_name(): string
    {
        return 'p4-cf-purge';
    }

    /**
     * The description shown in the argument's help.
     *
     * @return string The description text.
     */
    protected static function get_short_description(): string
    {
        return 'Purge urls from Cloudflare cache';
    }

    /**
     * The logic of the command. Has WP_CLI command signature.
     *
     * @param array|null $args Positional arguments.
     * @param array|null $assoc_args Named arguments.
     */
    public static function execute(?array $args, ?array $assoc_args): void
    {
        if (! Features\CloudflareDeployPurge::is_active()) {
            WP_CLI::warning('Purge on deploy is not enabled, not purging.');

            return;
        }

        $cf = new CloudflarePurger();
        $urls = self::get_urls($assoc_args);
        WP_CLI::log('About to purge ' . count($urls) . ' urls.');

        foreach ($cf->purge($urls) as $i => [$result, $chunk]) {
            WP_CLI::log('Chunk ' . $i . ': ' . ( $result ? 'ok' : 'failed' ));
            if (true !== $result) {
                $joined = implode("\n", $chunk);
                WP_CLI::warning("Chunk $i failed, one or more of these didn't work out: \n$joined");
            }
        }
    }

    /**
     * Determine which urls to purge. Throws error if right args were not passed.
     *
     * @param array|null $assoc_args The named args passed to the command.
     *
     * @throws \RuntimeException If you don't provide the right args.
     *
     * @return array The urls to purge
     */
    private static function get_urls(?array $assoc_args): array
    {
        if (isset($assoc_args['urls'])) {
            return explode(',', $assoc_args['urls']);
        }

        if (isset($assoc_args['all'])) {
            $post_types = ! empty($assoc_args['post-types'])
                ? explode(',', $assoc_args['post-types'])
                : null;
            return CloudflarePurger::get_all_urls($post_types);
        }

        throw new \RuntimeException('Please provide either --urls, or purge all urls with --all.');
    }
}
