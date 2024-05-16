<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search\Filters;

use P4\MasterTheme\ActionPage;
use WP_Query;

/**
 * Action types used for search (Petition, Event, etc.).
 */
class ActionTypes
{
    public const QUERY_ID = 'atype';
    public const TAXONOMY_ID = ActionPage::TAXONOMY;
    public const CONTEXT_ID = 'action_types';

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
    public static function get_filters(bool $new_ia): array
    {
        if (!$new_ia) {
            return [];
        }

        $types = self::get_all();
        $filters = [];

        foreach ($types as $type) {
            $filters[ $type->term_id ] = [
                'id' => $type->term_id,
                'slug' => $type->slug,
                'name' => $type->name,
                'results' => 0,
            ];
        }

        return $filters;
    }

    public static function apply_to_query(array $values, WP_Query $query): void
    {
        $query->set('post_type', ActionPage::POST_TYPE);

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
