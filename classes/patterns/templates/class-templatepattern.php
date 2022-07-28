<?php
/**
 * Template Pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns\Templates;

/**
 * This class is used for returning reusable blocks used in patterns.
 *
 * @package P4GBKS\Patterns\Templates
 */
abstract class TemplatePattern {
	/**
	 * Get the the reusable content.
	 *
	 * @param array $params Optional array of parameters for the content.
	 * @return string The string of the the pattern.
	 */
	abstract public static function get_content( $params ): string;
}
