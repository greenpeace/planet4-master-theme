<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use P4\MasterTheme\ActionPage;

/**
 * Action types used for search (Petition, Event, etc.).
 */
class ActionTypes
{
    /**
     * Get all content types.
     *
     * @return array<WP_Post_Type>
     */
    public static function get_all(): array
    {
        return get_terms(
            [
                'taxonomy' => ActionPage::TAXONOMY,
                'hide_empty' => false,
            ]
        );
    }

    /**
     * @return array{int, array{term_id: int, name: string, results: int}}
     */
    public static function get_filters(): array
    {
        $types = self::get_all();
        $filters = [];

        foreach ($types as $type) {
            $filters[ $type->term_id ] = [
                'term_id' => $type->term_id,
                'name' => $type->name,
                'results' => 0,
            ];
        }

        return $filters;
    }
}
