<?php

/**
 * Commands.
 */

namespace P4\MasterTheme;

use P4\MasterTheme\Commands\CloudflarePurge;
use P4\MasterTheme\Commands\RunActivator;
use P4\MasterTheme\Commands\SaveCloudflareKey;
use P4\MasterTheme\Commands\FixOrphans;
use P4\MasterTheme\Commands\GFAddonsDisconnect;
use P4\MasterTheme\Migrations;

/**
 * Class with a static function just because PHP can't autoload functions.
 */
class Commands
{
    /**
     * Add some WP_CLI commands if we're in CLI.
     */
    public static function load()
    {
        if (! defined('WP_CLI') || ! WP_CLI) {
            return;
        }
        RunActivator::register();
        SaveCloudflareKey::register();
        CloudflarePurge::register();
        FixOrphans::register();
        GFAddonsDisconnect::register();

        \WP_CLI::add_command(
            'p4-update-missing-media-path',
            function () {
                $record = MigrationRecord::start(static::class);
                Migrations\M004UpdateMissingMediaPath::execute($record);
            },
            [ 'shortdesc' => 'Updates missing media path after WPML activation.' ]
        );
    }
}
