<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search;

class Aggregations
{
    public const POST_PARENT = 'post_parent';
    public const POST_TYPE = 'post_type';
    public const CATEGORIES = 'category';
    public const TAGS = 'post_tag';
    public const P4_PAGE_TYPE = 'p4-page-type';
    public const ACTION_TYPE = 'action-type';

    /**
     * Match aggregation name and filter name to distribute count
     */
    public static function get_matching_filter(string $agg): ?string
    {
        return match ($agg) {
            self::CATEGORIES => Filters\Categories::CONTEXT_ID,
            self::POST_TYPE => Filters\ContentTypes::CONTEXT_ID,
            self::ACTION_TYPE => Filters\ActionTypes::CONTEXT_ID,
            self::P4_PAGE_TYPE => Filters\PostTypes::CONTEXT_ID,
            self::TAGS => Filters\Tags::CONTEXT_ID,
            default => null,
        };
    }
}
