<?php
/**
 * High-Level Topic pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

use P4GBKS\Patterns\Templates\Covers;
use P4GBKS\Patterns\Templates\GravityFormWithText;

/**
 * Class High-Level Topic.
 *
 * @package P4GBKS\Patterns
 */
class HighLevelTopic extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/high-level-topic-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 * We start with 3 columns, but editors can easily remove and/or duplicate them.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		$classname = self::get_classname();

		return [
			'title'      => 'High-Level Topic',
			'categories' => [ 'layouts' ],
			'blockTypes' => [ 'core/post-content' ],
			'content'    => '
				<!-- wp:group {"className":"block ' . $classname . '"} -->
					<div class="wp-block-group ' . $classname . '">
						' . PageHeader::get_config( [ 'title_placeholder' => __( 'Page header title', 'planet4-blocks' ) ] )['content'] . '
						<!-- wp:spacer {"height":"64px"} -->
							<div style="height:64px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						' . RealityCheck::get_config()['content'] . '
						' . SideImageWithTextAndCta::get_config(
							[
								'title'           => __( 'The problem', 'planet4-blocks' ),
								'backgroundColor' => 'grey-05',
								'alignFull'       => true,
							]
						)['content'] . '
						' . DeepDive::get_config(
							[
								'title'            => __( 'Better understand the issues [deep dive topics]', 'planet4-blocks' ),
								'background_color' => 'white',
							]
						)['content'] . '
						' . SideImageWithTextAndCta::get_config(
							[
								'title'           => __( 'What we do', 'planet4-blocks' ),
								'backgroundColor' => 'grey-05',
								'alignFull'       => true,
								'mediaPosition'   => 'right',
							]
						)['content'] . '
						' . HighlightedCta::get_config( [ 'titlePlaceholder' => __( 'Featured action title', 'planet4-blocks' ) ] )['content'] . '
						' . Covers::get_content(
							[
								'cover_type'        => 'take-action',
								'title_placeholder' => __( 'How you can help', 'planet4-blocks' ),
							]
						) . '
						<!-- wp:planet4-blocks/articles {"article_heading":"' . __( 'Latest news & stories', 'planet4-blocks' ) . '"} /-->
						' . Covers::get_content(
							[
								'cover_type'        => 'content',
								'title_placeholder' => __( 'Latest investigations', 'planet4-blocks' ),
							]
						) . '
						' . GravityFormWithText::get_content() . '
						' . QuickLinks::get_config(
							[
								'title'            => __( 'Explore by topics', 'planet4-blocks' ),
								'background_color' => 'white',
							]
						)['content'] . '
					</div>
				<!-- /wp:group -->
			',
		];
	}
}
