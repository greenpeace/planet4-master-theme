<?php

declare(strict_types=1);

namespace P4\MasterTheme\Search;

class ElasticSearch
{
    public static ?bool $is_active = null;

    public static function hooks(): void
    {
        if (!self::is_active()) {
            return;
        }

        // Enables facets for results aggregation
        add_action('ep_setup_features', function (): void {
            \ElasticPress\Features::factory()->update_feature('facets', [
                'active' => true,
                'match_type' => 'all',
            ]);
        });

        // Return more post fields
        add_filter('ep_search_post_return_args', function ($properties): array {
            $properties[] = 'guid';
            return $properties;
        });
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
