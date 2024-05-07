<?php

/**
 * Quick Links class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Quick Links.
 *
 * @package P4\MasterTheme\Patterns
 */
class QuickLinks extends BlockPattern
{
    /**
     * Returns the pattern name.
     */
    public static function get_name(): string
    {
        return 'p4/quick-links';
    }

    /**
     * Returns the pattern config.
     *
     * @param array $params Optional array of parameters for the config.
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Quick Links',
            'categories' => [ 'planet4' ],
            'content' => '
				<!-- wp:planet4-block-templates/quick-links ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
