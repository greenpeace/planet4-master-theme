<?php

/**
 * BlankPage pattern class.
 *
 * @package P4GBKS
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * This class is used for returning a blank page with a default content.
 *
 * @package P4GBKS\Patterns
 */
class BlankPage extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/blank-page-pattern-layout';
    }

    /**
     * Returns the pattern config.
     *
     * @param array $params Optional array of parameters for the config.
     * @phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Blank page',
            'blockTypes' => [ 'core/post-content' ],
            'categories' => [ 'layouts' ],
            'content' => '
				<!-- wp:paragraph {"placeholder":"' . __('Enter text', 'planet4-blocks-backend') . '"} -->
				<p></p>
				<!-- /wp:paragraph -->
			',
        ];
    }
    // @phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter.UnusedParameter
}
