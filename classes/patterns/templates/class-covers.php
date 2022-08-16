<?php
/**
 * Covers reusable pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns\Templates;

use P4GBKS\Patterns\Templates\TemplatePattern;

/**
 * This class is used for returning a blank page with a default content.
 *
 * @package P4GBKS\Patterns\Templates
 */
class Covers extends TemplatePattern {

	/**
	 * @inheritDoc
	 *
	 * @param array $params Optional array of parameters for the content.
	 */
	public static function get_content( $params = [] ): string {
		$title_placeholder = $params['title_placeholder'] ?? '';
		$cover_type        = $params['cover_type'] ?? 'take-action';

		return '<!-- wp:planet4-blocks/covers {
			"cover_type":"' . $cover_type . '",
			"className":"is-style-' . $cover_type . '",
			"title":"' . $title_placeholder . '"
		} -->
		<div
			class="wp-block-planet4-blocks-covers is-style-' . $cover_type . '"
			data-render="planet4-blocks/covers"
			data-attributes="{&quot;attributes&quot;:{&quot;cover_type&quot;:&quot;' . $cover_type . '&quot;,&quot;initialRowsLimit&quot;:1,&quot;title&quot;:&quot;' . $title_placeholder . '&quot;,&quot;description&quot;:&quot;&quot;,&quot;tags&quot;:[],&quot;post_types&quot;:[],&quot;posts&quot;:[],&quot;version&quot;:2,&quot;layout&quot;:&quot;grid&quot;,&quot;isExample&quot;:false,&quot;readMoreText&quot;:&quot;Load more&quot;,&quot;className&quot;:&quot;is-style-' . $cover_type . '&quot;},&quot;innerBlocks&quot;:[]}"></div>
		<!-- /wp:planet4-blocks/covers -->';
	}
}
