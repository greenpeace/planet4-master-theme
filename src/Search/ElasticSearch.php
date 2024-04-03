<?php

namespace P4\MasterTheme\Search;

class ElasticSearch
{
    public static ?bool $is_active = null;

    public static function hooks(): void
    {
        add_filter('ep_search_post_return_args', function ($args) {
                $args[] = 'guid';
                $args[] = 'terms';
                return $args;
        });

        add_filter('ep_formatted_args', function ($formatted_args) {
            $formatted_args['_source'] = [
                'post_id',
                'ID',
                'post_author',
                'post_date',
                'post_date_gmt',
                'post_title',
                'post_excerpt',
                'post_name',
                'post_modified',
                'post_modified_gmt',
                'post_content',
                'post_parent',
                'post_type',
                'post_mime_type',
                'permalink',
                'post_slug',
                'terms',
                'date_terms',
                'comment_count',
                'comment_status',
                'guid',
                'post_lang',
            ];

            return $formatted_args;
        }, 10, 1);
    }

    public static function is_active(): bool
    {
        if (self::$is_active === null) {
            self::$is_active = defined('EP_PATH')
                && class_exists('\\ElasticPress\\Elasticsearch')
                && \is_plugin_active('elasticpress/elasticpress.php');
        }
        return self::$is_active;
    }
}
