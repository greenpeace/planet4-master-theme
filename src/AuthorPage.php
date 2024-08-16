<?php

declare(strict_types=1);

namespace P4\MasterTheme;

/**
 * Handle author page main query
 */
class AuthorPage
{
    public static function hooks(): void
    {
        add_action(
            'pre_get_posts',
            [self::class, 'filter_author_main_query'],
            1,
            10
        );
    }

    public static function filter_author_main_query(\WP_Query $query): void
    {
        if (!$query->is_main_query() || !$query->is_author()) {
            return;
        }

        $query->set('posts_per_page', 10);
        $query->set('post_type', 'post');
        $query->set('meta_key', 'p4_author_override');
        $query->set('meta_compare', 'NOT EXISTS');
        $query->set('has_password', false);
    }
}
