<?php

/**
 * DeepDive class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class DeepDive.
 *
 * @package P4\MasterTheme\Patterns
 */
class DeepDive extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/deep-dive';
    }

    /**
     * @inheritDoc
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Deep Dive',
            'categories' => [ 'planet4' ],
            'content' => '
				<!-- wp:planet4-block-templates/deep-dive ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
