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
