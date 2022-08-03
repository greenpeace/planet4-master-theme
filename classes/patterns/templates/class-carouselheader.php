<?php
/**
 * CarouselHeader reusable pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4GBKS\Patterns\Templates;

use P4GBKS\Patterns\Templates\TemplatePattern;

/**
 * This class is used for returning a carousel header with a default content.
 *
 * @package P4GBKS\Patterns\Templates
 */
class CarouselHeader extends TemplatePattern {

	/**
	 * @inheritDoc
	 *
	 * @param array $params Optional array of parameters for the content.
	 */
	public static function get_content( $params = [] ): string {
		return '<!-- wp:planet4-blocks/carousel-header -->
				<div class="wp-block-planet4-blocks-carousel-header">
					<div data-hydrate="planet4-blocks/carousel-header" data-attributes="{&quot;carousel_autoplay&quot;:false,&quot;slides&quot;:[{&quot;image&quot;:null,&quot;focal_points&quot;:{},&quot;header&quot;:&quot;&quot;,&quot;description&quot;:&quot;&quot;,&quot;link_text&quot;:&quot;&quot;,&quot;link_url&quot;:&quot;&quot;,&quot;link_url_new_tab&quot;:false}]}" data-reactroot="">
						<section class="block block-header alignfull carousel-header ">
							<div class="carousel-wrapper-header">
								<div class="carousel-inner" role="listbox">
									<div class="carousel-item active">
										<div class="carousel-item-mask">
											<div class="background-holder">
												<img style="object-position:NaN% NaN%"/>
											</div>
											<div class="carousel-caption">
												<div class="caption-overlay"></div>
												<div class="container main-header">
													<div class="carousel-captions-wrapper">
														<h2></h2>
														<p></p>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</section>
					</div>
				</div>
			<!-- /wp:planet4-blocks/carousel-header -->';
	}
}
