<?php

declare(strict_types=1);

namespace P4\MasterTheme;

/**
 * Class TimberSettings
 *
 * Configures Timber-related settings such as class maps, Twig environment options, etc.
 */
class TimberSettings
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Registers filters to customize Timber behavior.
     */
    public function hooks(): void
    {
        // Map post types to custom Timber post class.
        add_filter('timber/post/classmap', function ($classmap) {
            $custom_classmap = [
                'page' => Post::class,
                'post' => Post::class,
                'campaign' => Post::class,
                'p4_action' => Post::class,
                'p4-page-type' => Post::class,
            ];

            return array_merge($classmap, $custom_classmap);
        });

        // Assign a custom Timber user class.
        add_filter('timber/user/class', fn() => User::class);

        // Customize Twig environment options.
        add_filter('timber/twig/environment/options', static function (array $options): array {
            $options['autoescape'] = 'html';
            $options['cache'] = defined('WP_DEBUG') ? !WP_DEBUG : true;
            return $options;
        });
    }
}
