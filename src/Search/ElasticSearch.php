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

        // Force enable facets for results aggregation
        add_action('ep_setup_features', static function (): void {
            if (self::facets_is_active()) {
                return;
            }

            \ElasticPress\Features::factory()->update_feature('facets', [
                'active' => true,
                'match_type' => 'all',
            ]);
        });

        // Push aggregation results to SearchPage
        add_action('ep_valid_response', static function ($response): void {
            SearchPage::$aggregations = $response['aggregations'] ?? [];
            SearchPage::$query_time = $response['took'] ?? null;
        }, 1, 10);

        // Return more post fields
        add_filter('ep_search_post_return_args', static function ($properties): array {
            $properties[] = 'guid';
            return $properties;
        });

        // Apply weighting decay function of time
        // Only works if feature "Weighting by date" is active
        add_filter('epwr_decay', static fn() => planet4_get_option('epwr_decay', 0.5));
        add_filter('epwr_scale', static fn() => planet4_get_option('epwr_scale', '28d'));
        add_filter('epwr_offset', static fn() => planet4_get_option('epwr_offset', '365d'));

        // Disable match fuzziness to avoid irrelevant results
        // Cf. https://elasticpress.zendesk.com/hc/en-us/articles/25809934420109-How-to-disable-fuzziness
        add_filter('ep_post_match_fuzziness', fn() => 0);

        /**
         * Fix the mapping for post_date and post_date_gmt fields.
         *
         * @param array $mapping The current mapping.
         * @return array The updated mapping.
         */
        $fix_post_date_mapping = static function (array $mapping): array {
            if (
                isset($mapping['mappings']['properties']['post_date']) &&
                "text" === $mapping['mappings']['properties']['post_date']['type']
            ) {
                $mapping['mappings']['properties']['post_date'] = [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd HH:mm:ss',
                ];
            }
            if (
                isset($mapping['mappings']['properties']['post_date_gmt']) &&
                "text" === $mapping['mappings']['properties']['post_date_gmt']['type']
            ) {
                $mapping['mappings']['properties']['post_date_gmt'] = [
                    'type' => 'date',
                    'format' => 'yyyy-MM-dd HH:mm:ss',
                ];
            }
            return $mapping;
        };

        add_filter('ep_config_mapping', $fix_post_date_mapping, 1, 10);
        add_filter('ep_post_mapping', $fix_post_date_mapping, 1, 10);

        add_action('ep_invalid_response', static function ($response): void {
            if (!function_exists('\Sentry\captureMessage')) {
                return;
            }

            if (is_wp_error($response)) {
                \Sentry\captureMessage(
                    'ElasticPress Query FAILED (WP_Error): ' . $response->get_error_message()
                );
                return;
            }

            if (isset($response['response']['code'], $response['body'])) {
                \Sentry\captureMessage(
                    'ElasticPress Query FAILED Response Code: ' .
                    $response['response']['code'] .
                    ' Response Body: ' . $response['body']
                );
            } else {
                \Sentry\captureMessage(
                    // phpcs:ignore Squiz.PHP.DiscouragedFunctions.Discouraged
                    'ElasticPress Query FAILED: Unknown response format: ' . print_r($response, true)
                );
            }
            // Check if indexing is in progress.
            if (\ElasticPress\Utils\get_indexing_status()) {
                return;
            }

            // Trigger a full index
            try {
                \ElasticPress\IndexHelper::factory()->full_index([
                    'put_mapping' => true,
                    'method' => 'dashboard',
                    'network_wide' => false,
                    'show_errors' => false,
                    'trigger' => 'manual',
                    'output_method' => [],
                ]);
            } catch (\Exception $e) {
                function_exists('\Sentry\captureException') && \Sentry\captureException($e);
            }
        }, 1, 10);
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

    public static function facets_is_active(): bool
    {
        if (!self::is_active()) {
            return false;
        }
        return \ElasticPress\Features::factory()->get_registered_feature('facets')->is_active();
    }
}
