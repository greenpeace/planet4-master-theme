<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search;

class Aggregations
{
    public const POST_PARENT = 'post_parent';
    public const POST_TYPE = 'post_type';
    public const CATEGORIES = 'category';
    public const TAGS = 'tags';
    public const P4_PAGE_TYPE = 'p4-page-type';
    public const ACTION_TYPE = 'action-type';
    public const WITH_POST_FILTER = 'with_post_filter';

    /**
     * All aggregations used to display total posts
     * by type/cat/etc. on result page
     */
    public static function get_all(): array
    {
        return [
            [
                'name' => self::POST_PARENT,
                'aggs' => [
                    'terms' => ['field' => 'post_parent',],
                ],
            ],
            [
                'name' => self::POST_TYPE,
                'aggs' => [
                    'terms' => ['field' => 'post_type.raw',],
                ],
            ],
            [
                'name' => self::CATEGORIES,
                'aggs' => [
                    'terms' => ['field' => 'terms.category.term_id',],
                ],
            ],
            [
                'name' => self::TAGS,
                'aggs' => [
                    'terms' => ['field' => 'terms.post_tag.term_id',],
                ],
            ],
            [
                'name' => self::P4_PAGE_TYPE,
                'aggs' => [
                    'terms' => ['field' => 'terms.p4-page-type.term_id',],
                ],
            ],
            [
                'name' => self::ACTION_TYPE,
                'aggs' => [
                    'terms' => ['field' => 'terms.action-type.term_id',],
                ],
            ],
        ];
    }

    /**
     * Require all aggregations to use post filter,
     * to match result count
     */
    public static function get_with_filter(): array
    {
        return [
            'name' => self::WITH_POST_FILTER,
            'use-filter' => true,
            'aggs' => array_merge(...array_map(
                function ($agg) {
                    return [$agg['name'] => $agg['aggs']];
                },
                self::get_all()
            )),
        ];
    }

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
