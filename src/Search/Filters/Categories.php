<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

/**
 * Categories used for search.
 */
class Categories
{
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
                'term_id' => $category->term_id,
                'name' => $category->name,
                'results' => 0,
            ];
        }

        return $filters;
    }
}
