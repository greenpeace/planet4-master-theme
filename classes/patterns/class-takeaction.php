<?php
/**
 * TakeAction pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns;

use P4GBKS\Patterns\Templates\Covers;

/**
 * Class TakeAction.
 *
 * @package P4GBKS\Patterns
 */
class TakeAction extends Block_Pattern {

	/**
	 * Returns the pattern name.
	 */
	public static function get_name(): string {
		return 'p4/take-action-pattern-layout';
	}

	/**
	 * Returns the pattern config.
	 *
	 * @param array $params Optional array of parameters for the config.
	 */
	public static function get_config( $params = [] ): array {
		$classname = self::get_classname();

		return [
			'title'      => __( 'Take Action', 'planet4-blocks-backend' ),
			'categories' => [ 'layouts' ],
			'blockTypes' => [ 'core/post-content' ],
			'content'    => '
				<!-- wp:group {"className":"block ' . $classname . '"} -->
					<div class="wp-block-group block ' . $classname . '">
						' . PageHeader::get_config( [ 'title_placeholder' => __( 'Page header title', 'planet4-blocks' ) ] )['content'] . '
						<!-- wp:spacer {"height":"64px"} -->
							<div style="height:64px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						<!-- wp:planet4-blocks/articles {
							"article_heading":"' . __( 'Daily actions', 'planet4-blocks' ) . '"
						} /-->
						<!-- wp:spacer {"height":"32px"} -->
							<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						' . Covers::get_content( [ 'title_placeholder' => __( 'Support a cause', 'planet4-blocks' ) ] ) . '
						<!-- wp:spacer {"height":"48px"} -->
							<div style="height:48px" aria-hidden="true" class="wp-block-spacer"></div>
						<!-- /wp:spacer -->
						<!-- wp:group {"backgroundColor":"grey-05","align":"full","style":{"spacing":{"padding":{"top":"64px","bottom":"64px"}}}} -->
							<div class="wp-block-group alignfull has-grey-05-background-color has-background" style="padding-top:64px;padding-bottom:64px;">
								<!-- wp:group {"className":"container"} -->
									<div class="wp-block-group container">
										<!-- wp:planet4-blocks/articles {
											"article_heading":"' . __( 'Take action with us', 'planet4-blocks' ) . '"
										} /-->
										<!-- wp:spacer {"height":"32px"} -->
											<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
										<!-- /wp:spacer -->
										' . DeepDive::get_config( [ 'title_placeholder' => __( 'Raise awareness in your community', 'planet4-blocks' ) ] )['content'] . '
										<!-- wp:spacer {"height":"32px"} -->
											<div style="height:32px" aria-hidden="true" class="wp-block-spacer"></div>
										<!-- /wp:spacer -->
										<!-- wp:planet4-blocks/articles {
											"article_heading":"' . __( 'Donate', 'planet4-blocks' ) . '"
										} /-->
									</div>
								<!-- /wp:group -->
							</div>
						<!-- /wp:group -->
					</div>
				<!-- /wp:group -->
			',
		];
	}
}
