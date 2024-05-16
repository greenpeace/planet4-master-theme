<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use WP_Query;

/**
 * Tags used for search.
 */
class Tags
{
    public const QUERY_ID = 'tag';
    public const CONTEXT_ID = 'tags';
    public const TAXONOMY_ID = 'post_tag';

    /**
     * @return WP_Term[]
     */
    public static function get_all(): array
    {
        $tags = get_terms(
            [
                'taxonomy' => 'post_tag',
                'hide_empty' => false,
            ]
        );

        if (is_wp_error($tags)) {
            return [];
        }

        return $tags;
    }

    /**
     * @return array{int, array{term_id: int, name: string, results: int}}
     */
    public static function get_filters(): array
    {
        $tags = self::get_all();
        $filters = [];

        foreach ($tags as $tag) {
            $filters[ $tag->term_id ] = [
                'id' => $tag->term_id,
                'slug' => $tag->slug,
                'name' => $tag->name,
                'results' => 0,
            ];
        }

        return $filters;
    }

    public static function apply_to_query(array $values, WP_Query $query): void
    {
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
