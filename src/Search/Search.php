<?php

namespace P4\MasterTheme\Search;

use WP_Query;
use P4\MasterTheme\PostArchive;
use P4\MasterTheme\ActionPage;

class Search
{
    public const POSTS_PER_LOAD = 5;
    public const DOCUMENT_TYPES = [
        'application/pdf',
    ];

    public static function hooks(): void
    {
        add_filter('pre_get_posts', [self::class, 'validate_query'], 10, 1);
        add_filter('pre_get_posts', [self::class, 'set_general_args'], 10, 1);
        add_filter('pre_get_posts', [self::class, 'exclude_page_for_posts'], 10, 1);
        add_filter('pre_get_posts', [self::class, 'exclude_unwanted_attachments'], 10, 1);

        SearchPage::hooks();
        if (!ElasticSearch::is_active()) {
            return;
        }

        ElasticSearch::hooks();
    }

    public static function validate_query(WP_Query $query): WP_Query
    {
        if (!$query->is_search()) {
            return $query;
        }

        if (
            isset($query->query_vars['orderby'])
            && $query->query_vars['orderby'] === 'post_date_asc'
        ) {
            $query->set('orderby', 'post_date');
            $query->set('order', 'ASC');
        }
        return $query;
    }

    public static function set_general_args(WP_Query $query): WP_Query
    {
        if (!$query->is_search()) {
            return $query;
        }

        //$query->set('no_found_rows', true);
        $query->set('posts_per_page', self::POSTS_PER_LOAD);
        $query->set('post_type', self::get_post_types());
        $query->set('post_status', [ 'publish', 'inherit' ]);
        $query->set('has_password', false); // Skip password protected content.

        return $query;
    }

    public static function exclude_page_for_posts(WP_Query $query): WP_Query
    {
        if (!$query->is_search()) {
            return $query;
        }

        $page_for_posts = get_option('page_for_posts');
        if (null !== $page_for_posts) {
            $query->set('post__not_in', [ $page_for_posts ]);
        }
        return $query;
    }

    public static function hide_password_protected_content(WP_Query $query): WP_Query
    {
        if (!$query->is_search()) {
            return $query;
        }

        $query->set('has_password', false);
        return $query;
    }

    public static function exclude_unwanted_attachments(WP_Query $query): WP_Query
    {
        if (!$query->is_search()) {
            return $query;
        }

        $query->set('post_mime_type', array_merge(self::DOCUMENT_TYPES, ['']));
        return $query;
    }

    /**
     * Get the post types that should be available in search.
     */
    private static function get_post_types(): array
    {
        $types = [
            'page',
            'campaign',
            'post',
            'attachment',
        ];

        if (self::should_include_archive()) {
            $types[] = PostArchive::POST_TYPE;
        }

        if (!empty(planet4_get_option('new_ia'))) {
            $types[] = ActionPage::POST_TYPE;
        }

        return $types;
    }

    /**
     * Whether archived content should be in the results.
     *
     * @return bool Whether archived content should be in the results.
     */
    private static function should_include_archive(): bool
    {
        $setting = planet4_get_option('include_archive_content_for');

        return 'all' === $setting || ( 'logged_in' === $setting && is_user_logged_in() );
    }
}
