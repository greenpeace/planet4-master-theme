<?php

/**
 * Homepage pattern layout class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * This class is used for returning a homepage pattern layout template.
 *
 * @package P4\MasterTheme\Patterns
 */
class Homepage extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/homepage-pattern-layout';
    }

    /**
     * @inheritDoc
     *
     * @param array $params Optional array of parameters for the config.
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Homepage',
            'blockTypes' => [ 'core/post-content' ],
            'categories' => [ 'layouts' ],
            'content' => '
				<!-- wp:planet4-block-templates/homepage ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
