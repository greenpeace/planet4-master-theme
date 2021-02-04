<?php

namespace P4\MasterTheme\Commands;

use P4\MasterTheme\Activator;

/**
 * Class RunActivator
 */
class RunActivator extends Command {

	/**
	 * The name to access the command.
	 *
	 * @return string The command name.
	 */
	protected static function get_name(): string {
		return 'p4-run-activator';
	}

	/**
	 * The logic of the command. Has WP_CLI command signature.
	 *
	 * @param array|null $args Positional arguments.
	 * @param array|null $assoc_args Named arguments.
	 */
	public static function execute( ?array $args, ?array $assoc_args ): void {
		Activator::run();
	}
}
