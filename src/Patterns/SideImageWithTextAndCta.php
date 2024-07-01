<?php

/**
 * Side image with text and CTA pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Side image with text and CTA.
 *
 * @package P4\MasterTheme\Patterns
 */
class SideImageWithTextAndCta extends BlockPattern
{
    /**
     * @inheritDoc
     */
    public static function get_name(): string
    {
        return 'p4/side-image-with-text-and-cta';
    }

    /**
     * @inheritDoc
     */
    public static function get_config(array $params = []): array
    {
        return [
            'title' => 'Side image with text and CTA',
            'categories' => [ 'planet4' ],
            'content' => '<!-- wp:planet4-block-templates/side-image-with-text-and-cta '
                . wp_json_encode($params, \JSON_FORCE_OBJECT)
                . ' /-->',
        ];
    }
}
