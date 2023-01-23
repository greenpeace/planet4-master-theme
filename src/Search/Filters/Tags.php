<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use WP_Term;

/**
 * Tags used for search.
 */
class Tags
{
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
                'term_id' => $tag->term_id,
                'name' => $tag->name,
                'results' => 0,
            ];
        }

        return $filters;
    }
}
