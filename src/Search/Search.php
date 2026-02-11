<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search;

use WP_Query;

class Search
{
    public const POSTS_PER_LOAD = 5;
    public const DOCUMENT_TYPES = ['application/pdf',];

    public const DEFAULT_SORT = '_score';
    public const DEFAULT_MIN_WEIGHT = 1;
    public const DEFAULT_PAGE_WEIGHT = 100;
    public const DEFAULT_ACTION_WEIGHT = 2000;
    public const DEFAULT_MAX_WEIGHT = 3000;
    public const MAX_SEARCH_LENGTH = 100;

    public const EXCLUDE_FROM_SEARCH = 'p4_do_not_index';

    public static function hooks(): void
    {
        add_filter('query_vars', [self::class, 'add_query_vars'], 10, 1);
        add_filter('pre_get_posts', [self::class, 'set_search_params'], 10, 1);
        add_filter('posts_where', [self::class, 'filter_mime_type'], 10, 2);
        add_action('wp_enqueue_scripts', [self::class, 'enqueue_search_scripts']);
        ElasticSearch::hooks();
    }

    private static function is_public_search(WP_Query $query): bool
    {
        return $query->is_search() && !\is_admin();
    }

    public static function add_query_vars(array $qvars): array
    {
        $qvars[] = 'f';
        return $qvars;
    }

    public static function set_search_params(WP_Query $query): WP_Query
    {
        if (!self::is_public_search($query)) {
            return $query;
        }

        if (!empty($query->query_vars['s'])) {
            $query->set('s', self::sanitize_query($query->query_vars['s']));
        }

        self::set_default_args($query);
        self::validate_filters($query);
        self::validate_order($query);
        self::exclude_page_for_posts($query);
        self::exclude_do_not_index($query);
        self::exclude_unwanted_attachments($query);

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

    public static function validate_filters(WP_Query $query): void
    {
        if (empty($query->query_vars['f']) || !is_array($query->query_vars['f'])) {
            return;
        }

        foreach ($query->query_vars['f'] as $type => $val) {
            if (!is_array($val)) {
                continue;
            }
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

    public static function exclude_page_for_posts(WP_Query $query): void
    {
        $page_for_posts = get_option('page_for_posts');
        if (null === $page_for_posts) {
            return;
        }

        $query->set('post__not_in', [$page_for_posts]);
    }

    public static function exclude_do_not_index(WP_Query $query): void
    {
        $meta_query = $query->get('meta_query');
        if (empty($meta_query)) {
            $meta_query = ['relation' => 'AND'];
        }

        $meta_query[] = [
            'key' => self::EXCLUDE_FROM_SEARCH,
            'compare' => 'NOT EXISTS',
        ];

        $query->set('meta_query', $meta_query);
    }

    public static function exclude_unwanted_attachments(WP_Query $query): void
    {
        $query->set('post_mime_type', array_merge(self::DOCUMENT_TYPES, ['']));
    }

    /**
     * This is only necessary if ElasticPress is not activated.
     * ElasticPress understands the filter set by `self::exclude_unwanted_attachments()`
     * but WordPress discards it during its function `wp_post_mime_type_where()`.
     */
    public static function filter_mime_type(string $where, WP_Query $query): string
    {
        if (!self::is_public_search($query)) {
            return $where;
        }

        global $wpdb;
        $types = (array) $query->get('post_mime_type');
        $escaped = array_map(fn($t) => '"' . $wpdb->esc_like($t) . '"', $types);
        $where .= ' AND post_mime_type IN(' . implode(',', $escaped) . ') ';
        return $where;
    }

    /**
     * Cf. WP admin: Planet 4 > Search
     */
    public static function should_include_archive(): bool
    {
        $setting = planet4_get_option('include_archive_content_for');
        return 'all' === $setting || ('logged_in' === $setting && is_user_logged_in());
    }

    /**
     * Sanitize query search
     * @param string the text taken from 's' query parameter
     * @return string sanitized query search
     */
    private static function sanitize_query(string $query_search): string
    {
        // Clean up query search
        $query_search = preg_replace('/[<>]/', '', $query_search);
        $query_search = sanitize_text_field($query_search);
        $query_search = esc_html($query_search);

        // Limit search query length to avoid long queries
        if (strlen($query_search) > self::MAX_SEARCH_LENGTH) {
            $query_search = substr($query_search, 0, self::MAX_SEARCH_LENGTH);
        }
        return $query_search;
    }

    /**
     * Get the post types that should be available in search.
     */
    private static function get_post_types(): array
    {
        $types = [
            Filters\ContentTypes::ATTACHMENT,
            Filters\ContentTypes::CAMPAIGN,
            Filters\ContentTypes::PAGE,
            Filters\ContentTypes::POST,
        ];

        if (self::should_include_archive()) {
            $types[] = Filters\ContentTypes::ARCHIVE;
        }

        if (!empty(planet4_get_option('new_ia'))) {
            $types[] = Filters\ContentTypes::ACTION;
        }

        return $types;
    }

    public static function enqueue_search_scripts(): void
    {
        if (!is_search()) {
            return;
        }
        wp_enqueue_script(
            'p4-search',
            get_template_directory_uri() . '/assets/build/search.js',
            [],
            '1.0',
            true
        );
        wp_enqueue_script(
            'p4-search-results',
            get_template_directory_uri() . '/assets/build/searchResults.js',
            ['wp-element'],
            '1.0',
            true
        );
    }
}
