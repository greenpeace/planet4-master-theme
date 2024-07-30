<?php

/**
 * Get Informed pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Get Informed.
 *
 * @package P4\MasterTheme\Patterns
 */
class GetInformed extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/get-informed-pattern-layout';
    }

    /**
     * @inheritDoc
     */
    public static function get_config($params = []): array
    {
        return [
            'title' => 'Get Informed',
            'blockTypes' => [ 'core/post-content' ],
            'categories' => [ 'layouts' ],
            'content' => '
				<!-- wp:planet4-block-templates/get-informed ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
