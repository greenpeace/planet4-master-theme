<?php

/**
 * Highlighted CTA pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Highlighted CTA.
 *
 * @package P4\MasterTheme\Patterns
 */
class HighlightedCta extends BlockPattern
{
    /**
     * Returns the pattern name.
     */
    public static function get_name(): string
    {
        return 'p4/highlighted-cta';
    }

    /**
     * Returns the pattern config.
     *
     * @param array $params Optional array of parameters for the config.
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Highlighted CTA',
            'categories' => [ 'planet4' ],
            'content' => '
				<!-- wp:planet4-block-templates/highlighted-cta ' . wp_json_encode($params, \JSON_FORCE_OBJECT) . ' /-->
			',
        ];
    }
}
