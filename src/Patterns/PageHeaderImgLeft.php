<?php

/**
 * Page Header pattern class.
 *
 * @package P4\MasterTheme\Patterns
 * @since 0.1
 */

namespace P4\MasterTheme\Patterns;

/**
 * Class Page Header.
 *
 * @package P4\MasterTheme\Patterns
 */
class PageHeaderImgLeft extends PageHeader
{
    protected static string $title = 'Page Header with image on the left';

    protected static string $media_position = 'left';

    /**
     * Returns the pattern name.
     */
    public static function get_name(): string
    {
        return 'p4/page-header-img-left';
    }
}
