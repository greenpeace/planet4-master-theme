<?php

namespace P4\MasterTheme\Commands;

use WP_CLI;

/**
 * Base class for WP_CLI commands.
 */
abstract class Command
{
    /**
     * Registers the command.
     *
     * @throws \Exception If WP_CLI doesn't like what we register.
     */
    public static function register(): void
    {
        WP_CLI::add_command(
            static::get_name(),
            [ static::class, 'execute' ],
            [
                'shortdesc' => static::get_short_description(),
            ]
        );
    }

    /**
     * The name to access the command.
     *
     * @return string The command name.
     */
    abstract protected static function get_name(): string;

    /**
     * The description shown in the argument's help.
     *
     * @return string The description text.
     */
    abstract protected static function get_short_description(): string;

    /**
     * The logic of the command. Has WP_CLI command signature.
     *
     * @param array|null $args Positional arguments.
     * @param array|null $assoc_args Named arguments.
     */
    abstract public static function execute(?array $args, ?array $assoc_args): void;
}
