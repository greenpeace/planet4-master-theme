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

        add_action('wp_enqueue_scripts', [$this, 'enqueue_async_sentry_sdk'], 10);
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

    /**
     * Enqueues the Sentry Browser SDK asynchronously, preserving dependencies,
     * version, and inline/localized data.
     *
     * Only targets scripts whose `src` ends with `wp-sentry-browser.min.js`.
     *
     */
    public function enqueue_async_sentry_sdk(): void
    {
        global $wp_scripts;

        if (empty($wp_scripts->registered)) {
            return;
        }

        foreach ($wp_scripts->registered as $handle => $script) {
            $src = $script->src ?? '';

            // Only target the Sentry browser SDK file
            if (! $src || ! str_ends_with($src, 'wp-sentry-browser.min.js')) {
                continue;
            }

            // Script metadata
            $localized = $script->extra['data'] ?? '';
            $deps = $script->deps ?? [];
            $ver = $script->ver ?? null;

            wp_deregister_script($handle);

            $async_handle = $handle . '-async';

            wp_register_script(
                $async_handle,
                $src,
                $deps,
                $ver,
                [
                    'strategy' => 'async',
                    'in_footer' => true,
                ]
            );

            if ($localized) {
                wp_add_inline_script($async_handle, $localized, 'before');
            }

            wp_enqueue_script($async_handle);
        }
    }
}
