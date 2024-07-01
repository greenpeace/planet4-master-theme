<?php

/**
 * Issues class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Issues.
 *
 * @package P4\MasterTheme\Patterns
 */
class Issues extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/issues';
    }

    /**
     * @inheritDoc
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Issues',
            'categories' => [ 'planet4' ],
            'content' => '
				<!-- wp:planet4-block-templates/issues ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
