<?php

declare(strict_types=1);

namespace P4\MasterTheme;

use Twig\Extension\StringLoaderExtension;
use Twig\Markup;

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
        add_filter('timber/twig', [$this, 'add_to_twig']);

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

    /**
     * Add your own functions to Twig.
     *
     * @param Twig_ExtensionInterface $twig The Twig object that implements the Twig_ExtensionInterface.
     *
     * @return mixed
     */
    public function add_to_twig(\Twig\Environment $twig)
    {
        $twig->addExtension(new StringLoaderExtension());

        $twig->addFilter(new \Twig\TwigFilter('svgicon', [$this, 'svgicon']));

        $twig->addFilter(new \Twig\TwigFilter('decode_entities', function ($str) {
            return html_entity_decode($str, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        }));

        return $twig;
    }

    /**
     * SVG Icon helper
     *
     * @param string $name Icon name.
     */
    public function svgicon(string $name): Markup
    {
        $svg_icon_template = '<svg viewBox="0 0 32 32" class="icon"><use xlink:href="'
            . get_template_directory_uri() . '/assets/build/sprite.symbol.svg#'
            . $name . '"></use></svg>';
        return new Markup($svg_icon_template, 'UTF-8');
    }
}
