<?php

/**
 * Action pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

 namespace P4\MasterTheme\Patterns;

/**
 * Class Action.
 *
 * @package P4\MasterTheme\Patterns
 */
class Action extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/action-pattern-layout';
    }

    /**
     * @inheritDoc
     */
    public static function get_config($params = []): array
    {
        return [
            'title' => 'Action',
            'categories' => [ 'layouts' ],
            'blockTypes' => [ 'core/post-content' ],
            'postTypes' => [ 'page', 'p4_action', 'campaign' ],
            'content' => '
				<!-- wp:planet4-block-templates/action ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
