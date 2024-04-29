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
     * Returns the pattern name.
     */
    public static function get_name(): string
    {
        return 'p4/reality-check';
    }

    /**
     * Returns the pattern config.
     *
     * @param array $params Optional array of parameters for the config.
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
