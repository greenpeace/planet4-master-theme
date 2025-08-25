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
     * Helper function to perform the ElasticPress full index.
     */
    private function sync_helper(): void
    {
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
        $multilingual = is_plugin_active('sitepress-multilingual-cms/sitepress.php');

        if ($multilingual) {
            // Get default language and active languages.
            $default_lang = apply_filters('wpml_default_language', null);
            $languages = apply_filters('wpml_active_languages', null, ['skip_missing' => 0]);

            foreach ($languages as $lang) {
                $lang_code = $lang['code'];
                do_action('wpml_switch_language', $lang_code);

                // Set the index name based on the language.
                // If the language is the default one, use the default index name.
                // Otherwise, append the language code to the default index name.
                $default_index = \ElasticPress\Indexables::factory()->get('post')->get_index_name();
                $lang_index = ($lang_code === $default_lang)
                    ? $default_index
                    : $default_index . '-' . $lang_code;
                // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter
                add_filter('ep_index_name', function ($anme) use ($lang_index) {
                    return $lang_index;
                });

                self::sync_helper();
                remove_all_filters('ep_index_name');
            }
        } else {
            self::sync_helper();
        }

        \Sentry\captureMessage(
            'P4 ES sync cronjob finish. ' . date("Y-m-d H:i:s")
        );
    }
}
