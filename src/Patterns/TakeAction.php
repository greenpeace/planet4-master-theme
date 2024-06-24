<?php

/**
 * TakeAction pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

 namespace P4\MasterTheme\Patterns;

/**
 * Class TakeAction.
 *
 * @package P4\MasterTheme\Patterns
 */
class TakeAction extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/take-action-pattern-layout';
    }

    /**
     * @inheritDoc
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Take Action',
            'categories' => ['layouts'],
            'blockTypes' => ['core/post-content'],
            'content' => '
                <!-- wp:planet4-block-templates/take-action ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
            ',
        ];
    }
}
