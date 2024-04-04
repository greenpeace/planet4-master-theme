<?php

declare(strict_types=1);

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

    public const DEFAULT_SORT = '_score';
    public const DEFAULT_MIN_WEIGHT = 1;
    public const DEFAULT_PAGE_WEIGHT = 100;
    public const DEFAULT_ACTION_WEIGHT = 2000;
    public const DEFAULT_MAX_WEIGHT = 3000;

    public static function hooks(): void
    {
        add_filter('query_vars', [self::class, 'add_query_vars'], 10, 1);
        add_filter('pre_get_posts', [self::class, 'set_search_params'], 10, 1);
        SearchPage::hooks();
        ElasticSearch::hooks();
    }

    public static function add_query_vars(array $qvars): array
    {
        $qvars[] = 'f';
        return $qvars;
    }

    public static function set_search_params(WP_Query $query): WP_Query
    {
        if (!$query->is_search()) {
            return $query;
        }

        self::set_default_args($query);
        self::validate_order($query);
        self::validate_filters($query);
        self::exclude_page_for_posts($query);
        self::exclude_unwanted_attachments($query);
        self::set_aggregations($query);

        return $query;
    }

    public static function set_default_args(WP_Query $query): void
    {
        $query->set('ep_integrate', true);
        $query->set('posts_per_page', self::POSTS_PER_LOAD);
        $query->set('post_type', self::get_post_types());
        $query->set('post_status', ['publish', 'inherit']);
        $query->set('has_password', false);
    }

    public static function validate_order(WP_Query $query): void
    {
        if (!in_array($query->query_vars['order'] ?? null, ['ASC', 'DESC'])) {
            $query->set('order', 'DESC');
        }

        // Empty search
        if (empty($query->query_vars['s'])) {
            $allowed = ['post_date', 'post_date_asc'];
            if (!in_array($query->query_vars['orderby'] ?? null, $allowed)) {
                $query->set('orderby', 'post_date');
                return;
            }
        }

        if (empty($query->query_vars['orderby'])) {
            $query->set('orderby', self::DEFAULT_SORT);
            return;
        }

        if (!in_array($query->query_vars['orderby'], ['post_date', 'post_date_asc', '_score'])) {
            $query->set('orderby', self::DEFAULT_SORT);
            return;
        }

        if ($query->query_vars['orderby'] === 'post_date_asc') {
            $query->set('orderby', 'post_date');
            $query->set('order', 'ASC');
            return;
        }
    }

    public static function validate_filters(WP_Query $query): void
    {
        if (empty($query->query_vars['f']) || !is_array($query->query_vars['f'])) {
            return;
        }

        foreach ($query->query_vars['f'] as $type => $val) {
            switch ($type) {
                case Filters\Categories::QUERY_ID:
                    Filters\Categories::apply_to_query(array_values($val), $query);
                    break;
                case Filters\Tags::QUERY_ID:
                    Filters\Tags::apply_to_query(array_values($val), $query);
                    break;
                case Filters\PostTypes::QUERY_ID:
                    Filters\PostTypes::apply_to_query(array_values($val), $query);
                    break;
                case Filters\ActionTypes::QUERY_ID:
                    Filters\ActionTypes::apply_to_query(array_values($val), $query);
                    break;
                case Filters\ContentTypes::QUERY_ID:
                    $ids = array_values($val);
                    $ctype_id = (int) array_pop($ids);
                    Filters\ContentTypes::apply_to_query($ctype_id, $query);
                    break;
                default:
                    break;
            }
        }
    }

    public static function exclude_page_for_posts(WP_Query $query): void
    {
        $page_for_posts = get_option('page_for_posts');
        if (null === $page_for_posts) {
            return;
        }

        $query->set('post__not_in', [$page_for_posts]);
    }

    public static function exclude_unwanted_attachments(WP_Query $query): void
    {
        $query->set('post_mime_type', array_merge(self::DOCUMENT_TYPES, ['']));
    }

    public static function set_aggregations(WP_Query $query): void
    {
        $query->set('aggs', Aggregations::get_with_filter());
    }

    public static function should_include_archive(): bool
    {
        $setting = planet4_get_option('include_archive_content_for');
        return 'all' === $setting || ('logged_in' === $setting && is_user_logged_in());
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
}
