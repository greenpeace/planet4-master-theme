<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use WP_Query;
use WP_Term;

/**
 * Post types used for search (Press release, News, Report, etc.).
 * Configured in `Posts > Posts Types`
 */
class PostTypes
{
    public const QUERY_ID = 'ptype';
    public const CONTEXT_ID = 'post_types';
    public const TAXONOMY_ID = 'p4-page-type';

    /**
     * @return WP_Term[]
     */
    public static function get_all(): array
    {
        $types = get_terms(
            [
                'taxonomy' => 'p4-page-type',
                'hide_empty' => false,
            ]
        );

        if (
            is_wp_error($types)
            || ! is_array($types)
            || empty($types)
            || ! ( $types[0] instanceof WP_Term )
        ) {
            return [];
        }

        return $types;
    }

    /**
     * @return array{int, array{term_id: int, name: string, results: int}}
     */
    public static function get_filters(): array
    {
        $post_types = self::get_all();
        $filters = [];

        foreach ($post_types as $post_type) {
            $filters[ $post_type->term_id ] = [
                'id' => $post_type->term_id,
                'slug' => $post_type->slug,
                'name' => $post_type->name,
                'results' => 0,
            ];
        }

        return $filters;
    }

    public static function apply_to_query(array $values, WP_Query $query): void
    {
        $query->set('post_type', 'post');

        $tax_query = $query->get('tax_query');
        if (empty($tax_query)) {
            $tax_query = ['operator' => 'AND'];
        }

        $tax_query[] = [
            'taxonomy' => self::TAXONOMY_ID,
            'field' => 'term_id',
            'terms' => array_map('intval', $values),
        ];

        $query->set('tax_query', $tax_query);
    }
}
