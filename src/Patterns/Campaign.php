<?php

/**
 * Campaign pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Campaign.
 *
 * @package P4\MasterTheme\Patterns
 */
class Campaign extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/campaign-pattern-layout';
    }

    /**
     * @inheritDoc
     */
    public static function get_config($params = []): array
    {
        return [
            'title' => 'Campaign',
            'blockTypes' => [ 'core/post-content' ],
            'categories' => [ 'layouts' ],
            'content' => '
				<!-- wp:planet4-block-templates/campaign ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
