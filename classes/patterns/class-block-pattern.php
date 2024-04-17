<?php
/**
 * Base pattern class.
 *
 * @package P4GBKS
 */

namespace P4GBKS\Patterns;

use P4GBKS\Search\Pattern\PatternData;

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
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	abstract public static function get_config( $params = [] ): array;

	/**
	 * Returns the pattern classname used for tracking patterns.
	 */
	public static function get_classname(): string {
		return PatternData::make_classname( static::get_name() );
	}

	/**
	 * Patterns list.
	 */
	public static function get_list(): array {
		return [
			BlankPage::class,
			DeepDiveTopic::class,
			GetInformed::class,
			Homepage::class,
			HighlightedCta::class,
			Issues::class,
			PageHeader::class,
			PageHeaderImgLeft::class,
			QuickLinks::class,
			RealityCheck::class,
			SideImageWithTextAndCta::class,
			Action::class,
			HighLevelTopic::class,
			Campaign::class,
			TakeAction::class,
		];
	}

	/**
	 * Pattern constructor.
	 */
	public static function register_all() {
		if ( ! function_exists( 'register_block_pattern' ) ) {
			return;
		}

		$patterns = self::get_list();

		/**
		 * @var $pattern self
		 */
		foreach ( $patterns as $pattern ) {
			register_block_pattern( $pattern::get_name(), $pattern::get_config() );
		}
	}
}
