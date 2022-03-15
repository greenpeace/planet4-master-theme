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
abstract class Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	abstract public static function get_name(): string;

	/**
	 * Returns the pattern config.
	 */
	abstract public static function get_config(): array;

	/**
	 * Pattern constructor.
	 */
	public static function register_all() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		$patterns = [
			SideImageWithTextAndCta::class,
			HighlightedCta::class,
			RealityCheck::class,
			Issues::class,
		];

		/**
		 * @var $pattern self
		 */
		foreach ( $patterns as $pattern ) {
			register_block_pattern( $pattern::get_name(), $pattern::get_config() );
		}
	}
}
