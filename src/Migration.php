<?php

namespace P4\MasterTheme;

/**
 * A migration that can be recorded.
 */
abstract class Migration {
	/**
	 * Get a unique identifier, achieved here by using the FQCN.
	 *
	 * @return string The unique identifier.
	 */
	public static function get_id(): string {
		return static::class;
	}

	/**
	 * Perform the actual migration.
	 */
	abstract public static function run(): void;
}
