<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use WP_Query;

/**
 * Categories used for search.
 */
class Categories
{
    public const QUERY_ID = 'cat';
    public const TAXONOMY_ID = 'category';
    public const CONTEXT_ID = 'categories';

    /**
     * @return array<WP_Term>
     */
    public static function get_all(): array
    {
        return get_categories();
    }

    /**
     * @return array{int, array{term_id: int, name: string, results: int}}
     */
    public static function get_filters(): array
    {
        $categories = self::get_all();
        $filters = [];

        foreach ($categories as $category) {
            if ('uncategorised' === $category->slug) {
                continue;
            }

            $filters[ $category->term_id ] = [
                'id' => $category->term_id,
                'slug' => $category->slug,
                'name' => $category->name,
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
            'operator' => 'AND',
        ];

        $query->set('tax_query', $tax_query);
    }
}
