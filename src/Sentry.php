<?php

namespace P4\MasterTheme;

/**
 * Class Sentry
 */
class Sentry
{
    // Sample rate for Sentry error reporting
    public const SENTRY_SAMPLE_RATE = 0.50;

    /**
     * Sentry constructor.
     */
    public function __construct()
    {
        if (!class_exists('\\Sentry\\Options')) {
            return;
        }

        add_filter('wp_sentry_options', function (\Sentry\Options $options) {
            $options->setSampleRate(self::SENTRY_SAMPLE_RATE);

            $options->setServerName($this->server_name());

            return $options;
        });

        add_filter('wp_sentry_public_options', function (array $options) {
            return array_merge($options, array(
                'sampleRate' => self::SENTRY_SAMPLE_RATE,
            ));
        });

        add_filter('wp_sentry_public_context', function (array $context) {
            $context['tags']['server_name'] = $this->server_name();
            return $context;
        });

        add_action('wp_enqueue_scripts', function (): void {
            global $wp_scripts;

            $sentry_handles = [];
            foreach ($wp_scripts->registered as $handle => $data) {
                if (strpos($handle, 'wp-sentry-browser') !== 0) {
                    continue;
                }

                $sentry_handles[] = $handle;
            }

            if (empty($sentry_handles)) {
                return;
            }

            foreach ($sentry_handles as $handle) {
                $localized = $wp_scripts->registered[$handle]->extra['data'] ?? '';

                wp_deregister_script($handle);

                $async_handle = $handle . '-async';

                $src = $wp_scripts->registered[$handle]->src ?? '';

                wp_register_script(
                    $async_handle,
                    $src,
                    [],
                    null,
                    true
                );

                wp_script_add_data($async_handle, 'strategy', 'async');

                if ($localized) {
                    wp_add_inline_script($async_handle, $localized, 'before');
                }

                wp_enqueue_script($async_handle);
            }
        }, 10);
    }

    /**
     * Determines the server name based on the hostname.
     *
     * @return string The server name.
     */
    public function server_name(): string
    {
        $podname = gethostname() ?: 'unknown'; // Fallback to 'unknown' if gethostname() fails
        $parts = explode('-', $podname);

        if (count($parts) === 1) {
            // Local development
            return $podname;
        }
        if ($parts[1] === 'test') {
            // Test instances
            return $parts[2];
        }

        // Production/Development instances
        return $parts[1];
    }
}
