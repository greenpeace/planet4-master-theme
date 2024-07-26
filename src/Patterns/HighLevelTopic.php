<?php

/**
 * High Level Topic pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

 namespace P4\MasterTheme\Patterns;

/**
 * Class High Level Topic.
 *
 * @package P4\MasterTheme\Patterns
 */
class HighLevelTopic extends BlockPattern
{
    /**
     * Returns the pattern name.
     */
    public static function get_name(): string
    {
        return 'p4/high-level-topic-pattern-layout';
    }

    /**
     * Returns the pattern config.
     *
     * @param array $params Optional array of parameters for the config.
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'High-Level Topic',
            'categories' => [ 'layouts' ],
            'blockTypes' => [ 'core/post-content' ],
            'content' => '
                <!-- wp:planet4-block-templates/high-level-topic '
                    . wp_json_encode($params, \JSON_FORCE_OBJECT) .
                ' /-->
            ',
        ];
    }
}
