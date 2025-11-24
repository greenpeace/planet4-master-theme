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

            $localized = null;
            if (isset($wp_scripts->registered['wp-sentry-browser']->extra['data'])) {
                $localized = $wp_scripts->registered['wp-sentry-browser']->extra['data'];
            }

            wp_dequeue_script('wp-sentry-browser');
            wp_deregister_script('wp-sentry-browser');

            $src = plugins_url(
                'public/wp-sentry-browser.min.js',
                WP_PLUGIN_DIR . '/wp-sentry-integration/wp-sentry-integration.php'
            );

            wp_register_script(
                'wp-sentry-browser-async',
                $src,
                [],
                null,
                true
            );

            if ($localized) {
                $wp_scripts->registered['wp-sentry-browser-async']->extra['data'] = $localized;
            }

            add_filter('script_loader_tag', function ($tag, $handle) {
                if ('wp-sentry-browser-async' === $handle) {
                    return str_replace('<script ', '<script async ', $tag);
                }
                return $tag;
            }, 10, 2);

            wp_enqueue_script('wp-sentry-browser-async');
        }, 50);
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
