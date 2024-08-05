<?php

/**
 * DeepDiveTopic class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class DeepDiveTopic.
 *
 * @package P4\MasterTheme\Patterns
 */
class DeepDiveTopic extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/deep-dive-topic-pattern-layout';
    }

    /**
     * @inheritDoc
     */
    public static function get_config($params = []): array
    {
        return [
            'title' => 'Deep Dive Topic',
            'categories' => [ 'layouts' ],
            'blockTypes' => [ 'core/post-content' ],
            'content' => '
				<!-- wp:planet4-block-templates/deep-dive-topic ' .
                wp_json_encode($params, \JSON_FORCE_OBJECT) .
                ' /-->',
        ];
    }
}
