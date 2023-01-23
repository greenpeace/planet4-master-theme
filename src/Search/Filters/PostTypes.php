<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use WP_Term;

/**
 * Post types used for search (Press release, News, Report, etc.).
 * Configured in `Posts > Posts Types`
 */
class PostTypes
{
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
                'term_id' => $post_type->term_id,
                'name' => $post_type->name,
                'results' => 0,
            ];
        }

        return $filters;
    }
}
