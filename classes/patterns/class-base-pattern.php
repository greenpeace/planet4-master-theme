<?php
/**
 * Base pattern class.
 *
 * @package P4GBKS
 */

namespace P4GBKS\Patterns;

/**
 * Class Base_Pattern
 *
 * @package P4GBKS\Patterns
 */
abstract class Base_Pattern {

	public const NAMESPACE = 'planet4-patterns';

	/**
	 * Returns the pattern name with its namespace prefix, e.g.: planet4-patterns/side-image-with-text-and-cta.
	 */
	public static function get_full_pattern_name() {
		return static::NAMESPACE . '/' . static::PATTERN_NAME;
	}
}
