<?php

/**
 * Chart class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Chart.
 *
 * @package P4\MasterTheme\Patterns
 */
class Chart extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/chart';
    }

    /**
     * @inheritDoc
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Chart',
            'categories' => [ 'planet4' ],
            'content' => '
				<!-- wp:planet4-block-templates/chart ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
