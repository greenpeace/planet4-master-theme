<?php

namespace P4\MasterTheme;

/**
 * Class CronJob
 */
class CronJob
{
    /**
     * Constructor.
     */
    public function __construct()
    {
        add_action('p4_full_es_sync_action', [ $this, 'p4_full_es_sync' ], 10);
    }

    /**
     * Triggers a full Elastic search indexing.
     */
    public function p4_full_es_sync(): void
    {
        // Check if indexing is in progress.
        if (\ElasticPress\Utils\get_indexing_status()) {
            \Sentry\captureMessage(
                'ES indexing already in progress. ' . date("Y-m-d H:i:s")
            );
            return;
        }

        \Sentry\captureMessage(
            'P4 ES sync cronjob started. ' . date("Y-m-d H:i:s")
        );

        // Trigger a full index.
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

        \Sentry\captureMessage(
            'P4 ES sync cronjob finish. ' . date("Y-m-d H:i:s")
        );
    }
}
