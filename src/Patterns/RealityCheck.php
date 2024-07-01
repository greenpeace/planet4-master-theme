<?php

/**
 * Reality Check class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Reality Check.
 *
 * @package P4\MasterTheme\Patterns
 */
class RealityCheck extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/reality-check';
    }

    /**
     * @inheritDoc
     */
    public static function get_config(array $params = []): array
    {

        return [
            'title' => 'Reality Check',
            'categories' => [ 'planet4' ],
            'content' => '
				<!-- wp:planet4-block-templates/reality-check ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
