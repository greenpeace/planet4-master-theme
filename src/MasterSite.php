<?php

namespace P4\MasterTheme;

use P4\MasterTheme\Features\Dev\CoreBlockPatterns;
use Timber\Timber;
use Twig\Extension\StringLoaderExtension;
use Twig\Markup;
use WP_Error;

/**
 * Class MasterSite.
 * The main class that handles Planet4 Master Theme.
 */
class MasterSite extends \Timber\Site
{
    /**
     * MasterSite constructor.
     */
    public function __construct()
    {
        $this->settings();
        $this->external_hooks();
        $this->hooks();
        parent::__construct();
    }

    /**
     * Define settings for the Planet4 Master Theme.
     */
    protected function settings(): void
    {
        Timber::$dirname = ['templates', 'templates/blocks', 'views'];
    }

    /**
     * Hooks from external classes.
     */
    protected function external_hooks(): void
    {
        Context::hooks($this);
        AuthorPage::hooks();
        BreakpointsImageSizes::hooks();
        QueryLoopPagination::hooks();
        Search\Search::hooks();
        Sendgrid::hooks();
    }

    /**
     * Hooks the theme.
     */
    protected function hooks(): void
    {
        add_theme_support('post-thumbnails');
        add_theme_support('menus');
        if (!CoreBlockPatterns::is_active()) {
            // Disable WP Block Patterns.
            remove_theme_support('core-block-patterns');
        }

        add_post_type_support('page', 'excerpt'); // Added excerpt option to pages.
        add_filter('timber/twig', [$this, 'add_to_twig']);
        add_action('init', [$this, 'register_taxonomies'], 2);
        add_action('init', [$this, 'register_oembed_provider']);
        add_action('admin_menu', [$this, 'add_post_revisions_setting']);
        // Load the editor scripts only enqueuing editor scripts while in context of the editor.
        add_action('enqueue_block_editor_assets', [$this, 'enqueue_editor_assets']);
        // Load main theme assets before any child theme.
        add_action('wp_enqueue_scripts', [PublicAssets::class, 'enqueue_css'], 0);
        add_action('wp_enqueue_scripts', [PublicAssets::class, 'enqueue_js']);
        add_filter('safe_style_css', [$this, 'set_custom_allowed_css_properties']);
        add_filter('wp_kses_allowed_html', [$this, 'set_custom_allowed_attributes_filter'], 10, 2);
        add_filter('wp_insert_post_data', [$this, 'require_post_title'], 10, 1);
        add_action('init', [$this, 'p4_master_theme_setup']);
        add_action('pre_insert_term', [$this, 'disallow_insert_term'], 1, 2);
        add_filter('wp_dropdown_users_args', [$this, 'filter_authors'], 10, 1);
        add_filter('http_request_timeout', fn () => 10);

        register_nav_menus(
            [
                'navigation-bar-menu' => __('Navigation Bar Menu', 'planet4-master-theme-backend'),
                'donate-menu' => __('Donate Button', 'planet4-master-theme-backend'),
                'footer-primary-menu' => __('Footer Primary Menu', 'planet4-master-theme-backend'),
                'footer-secondary-menu' => __('Footer Secondary Menu', 'planet4-master-theme-backend'),
                'footer-social-menu' => __('Footer Social Menu', 'planet4-master-theme-backend'),
            ]
        );

        add_filter(
            'editable_roles',
            function ($roles) {
                uasort(
                    $roles,
                    function ($a, $b) {
                        return $b['name'] <=> $a['name'];
                    }
                );
                return $roles;
            }
        );

        /**
         * Apply wpautop to non-block content.
         *
         * @link https://wordpress.stackexchange.com/q/321662/26317
         *
         * @param string $block_content The HTML generated for the block.
         * @param array  $block         The block.
         */
        add_filter(
            'render_block',
            function ($block_content, $block) {
                if (is_null($block['blockName'])) {
                    return wpautop($block_content);
                }
                return $block_content;
            },
            10,
            2
        );

        // Disable WordPress(WP5.5) Block Directory.
        $this->disable_block_directory();

        // Update P4 author override value in RSS feed.
        add_filter(
            'the_author',
            function ($post_author) {
                if (is_feed()) {
                    global $post;
                    $author_override = get_post_meta($post->ID, 'p4_author_override', true);
                    if ('' !== $author_override) {
                        $post_author = $author_override;
                    }
                }
                return $post_author;
            }
        );

        // Disable xmlrpc.
        add_filter('xmlrpc_enabled', '__return_false');

        // Anonymize Comment Authors IP address.
        add_filter(
            'pre_comment_user_ip',
            function () {
                return '';
            }
        );

        // Make post tags ordered.
        add_filter('register_taxonomy_args', [$this, 'set_post_tags_as_ordered'], 10, 2);

        add_action(
            'customize_register',
            function ($wp_customize): void {
                // Remove site icon customization.
                $wp_customize->remove_control('site_icon');

                if (!defined('WP_APP_ENV') || ( 'production' !== WP_APP_ENV && 'staging' !== WP_APP_ENV )) {
                    return;
                }

                // Disable CSS Customizer.
                $wp_customize->remove_control('custom_css');
            }
        );

        // Admin scripts.
        add_action('admin_enqueue_scripts', [AdminAssets::class, 'enqueue_js']);

        // Disable Background updates check in Site health.
        add_filter(
            'site_status_tests',
            function ($tests) {
                unset($tests['async']['background_updates']);
                return $tests;
            }
        );

        // Maximum revisions to keep whenever the editor save a post
        add_filter(
            'wp_revisions_to_keep',
            function ($revisions) {
                $revisions_to_keep = get_option('revisions_to_keep');
                return $revisions_to_keep ?: $revisions;
            },
            10,
            1
        );

        // Override category template to use taxonomy.php.
        add_filter(
            'template_include',
            function ($tax_template) {
                if (!is_category()) {
                    return $tax_template;
                }
                return get_template_directory() . '/taxonomy.php';
            }
        );

        // Fix WPML-related RTL issue.
        $remove_rtl_fix = function (): void {
            global $sitepress;
            // This RTL fix does not seem a good idea.
            // Probably it was a bad attempt at solving the issues `url_to_postid` creates.
            remove_action('wp_head', [$sitepress, 'rtl_fix']);
            remove_action('admin_print_styles', [$sitepress, 'rtl_fix']);

            // This caused `switch_lang` to get called. As a result the RTL fix messed up.
            remove_filter('url_to_postid', [$sitepress, 'url_to_postid']);
        };

        $remove_rtl_fix();
        add_action('wpml_after_startup', $remove_rtl_fix, 10, 0);

        // Enable Transparent nav for homepage
        add_filter(
            'body_class',
            function ($classes) {
                $enable_transparent_nav = !empty(planet4_get_option('transparent_nav'));

                if (is_front_page() && $enable_transparent_nav) {
                    $classes[] = 'transparent-nav';
                }

                return $classes;
            }
        );
    }

    /**
     * Make post title mandatory on publish.
     */
    public static function require_post_title(array $data): ?array
    {
        // Skip the post title requirement if the global variable is set.
        if (!empty($GLOBALS['p4_skip_require_post_title'])) {
            return $data;
        }

        $types = Search\Filters\ContentTypes::get_all();
        if (
            empty($data['post_title'])
            && !empty($data['post_status'])
            && $data['post_status'] === 'publish'
            && in_array($data['post_type'], array_keys($types))
        ) {
            $err_message = __('Title is a required field.', 'planet4-master-theme-backend');
            defined('WP_CLI') && WP_CLI
                ? throw new \Exception($err_message)
                : wp_die($err_message);
        }

        return $data;
    }

    /**
     * Generates hreflang metadata from countries.json template.
     */
    public static function generate_hreflang_meta(): ?array
    {

        $countries = wp_cache_get('countries');

        if (false === $countries) {
            $body = file_get_contents(get_template_directory() . '/templates/countries.json');
            $countries = json_decode($body, true);
            if (empty($countries)) {
                return null;
            }
            wp_cache_set('countries', $countries);
        }

        $metadata = [];

        foreach ($countries as $key => $letter) {
            if (0 === $key) {
                continue;
            }
            foreach ($letter as $country) {
                $lang = $country['lang'];
                foreach ($lang as $item) {
                    if (!isset($item['locale'])) {
                        continue;
                    }

                    foreach ($item['locale'] as $code) {
                        $metadata[$code] = $item['url'];
                    }
                }
            }
        }

        return $metadata;
    }

    /**
     * Load translations for master theme
     */
    public function p4_master_theme_setup(): void
    {
        $domains = [
            'planet4-master-theme',
            'planet4-master-theme-backend',
            'blocks/planet4-blocks',
            'blocks/planet4-blocks-backend',
        ];
        $locale = is_admin() ? get_user_locale() : get_locale();

        foreach ($domains as $domain) {
            $mofile = get_template_directory() . '/languages/' . $domain . '-' . $locale . '.mo';
            //TODO: Temporary fix.
            load_textdomain(str_replace('blocks/', '', $domain), $mofile);
        }
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

    /**
     * Set CSS properties that should be allowed for posts filter
     * Allow img object-position.
     *
     * @param array $allowedproperties Default allowed CSS properties.
     *
     */
    public function set_custom_allowed_css_properties(array $allowedproperties): array
    {
        $allowedproperties[] = 'object-position';
        $allowedproperties[] = '--spreadsheet-header-background';
        $allowedproperties[] = '--spreadsheet-even-row-background';
        $allowedproperties[] = '--spreadsheet-odd-row-background';

        return $allowedproperties;
    }

    /**
     * Set HTML attributes that should be allowed for posts filter
     * Allow img srcset and sizes attributes.
     * Allow iframes in posts.
     *
     * @see wp_kses_allowed_html()
     *
     * @param array  $allowedposttags Default allowed tags.
     * @param string $context         The context for which to retrieve tags.
     *
     */
    public function set_custom_allowed_attributes_filter(array $allowedposttags, string $context): array
    {
        if ('post' !== $context) {
            return $allowedposttags;
        }

        // Allow iframes and the following attributes.
        $allowedposttags['style'] = [];
        $allowedposttags['iframe'] = [
            'align' => true,
            'width' => true,
            'height' => true,
            'frameborder' => true,
            'name' => true,
            'src' => true,
            'srcdoc' => true,
            'id' => true,
            'class' => true,
            'style' => true,
            'scrolling' => true,
            'marginwidth' => true,
            'marginheight' => true,
            'allowfullscreen' => true,
        ];

        // Allow blockquote and the following attributes. (trigger: allow instagram embeds).
        $allowedposttags['blockquote'] = [
            'style' => true,
            'data-instgrm-captioned' => true,
            'data-instgrm-permalink' => true,
            'data-instgrm-version' => true,
            'class' => true,
        ];

        // Allow img and the following attributes.
        $allowedposttags['img'] = [
            'alt' => true,
            'class' => true,
            'id' => true,
            'height' => true,
            'hspace' => true,
            'name' => true,
            'src' => true,
            'srcset' => true,
            'sizes' => true,
            'width' => true,
            'style' => true,
            'vspace' => true,
            'loading' => true,
        ];

        $allowedposttags['script'] = [
            'src' => true,
            'id' => true,
            'data-*' => true,
            'onload' => true,
        ];

        // Allow source tag for WordPress audio shortcode to function.
        $allowedposttags['source'] = [
            'type' => true,
            'src' => true,
        ];

        // Allow below tags for carousel slider.
        $allowedposttags['li']['data-bs-target'] = true;
        $allowedposttags['li']['data-bs-slide-to'] = true;
        $allowedposttags['a']['data-bs-slide'] = true;
        $allowedposttags['span']['aria-hidden'] = true;

        // Allow below tags for spreadsheet block.
        $allowedposttags['input'] = [
            'class' => true,
            'type' => true,
            'placeholder' => true,
        ];

        $allowedposttags['lite-youtube'] = [
            'videoid' => true,
            'params' => true,
            'style' => true,
        ];

        // Allow object tag with some attributes.
        $allowedposttags['object'] = [
            'class' => true,
            'data' => true,
            'id' => true,
            'type' => true,
            'style' => true,
            'aria-label' => true,
        ];

        return $allowedposttags;
    }

    /**
     * Sanitizes the settings input.
     *
     * @param string $setting The setting to sanitize.
     *
     * @return string The sanitized setting.
     */
    public function sanitize(string $setting): string
    {
        $allowed = [
            'ul' => [],
            'ol' => [],
            'li' => [],
            'strong' => [],
            'del' => [],
            'span' => [
                'style' => [],
            ],
            'p' => [
                'style' => [],
            ],
            'a' => [
                'href' => [],
                'target' => [],
                'rel' => [],
            ],
        ];
        return wp_kses($setting, $allowed);
    }

    /**
     * Registering into the Settings > Writing setting page.
    */
    public function add_post_revisions_setting(): void
    {
        register_setting('writing', 'revisions_to_keep');

        add_settings_field(
            'post-revisions-field',
            __('Post revisions', 'planet4-master-theme-backend'),
            function ($val): void {
                $id = $val['id'];
                $option_name = $val['option_name'];
                ?>
                    <input
                        type="number"
                        name="<?php echo esc_attr($option_name) ?>"
                        id="<?php echo esc_attr($id) ?>"
                        value="<?php echo esc_attr(get_option($option_name)) ?>"
                    />
                    <span>
                        <?php echo __(
                            'Maximum number of revisions to store for each post.',
                            'planet4-master-theme-backend'
                        )?>
                    </span>
                <?php
            },
            'writing',
            'default',
            array(
                'id' => 'post-revisions-field',
                'option_name' => 'revisions_to_keep',
            )
        );
    }

    /**
     * Add resources for the Gutenberg editor.
     */
    public function enqueue_editor_assets(): void
    {
        Loader::enqueue_versioned_style('assets/build/editorStyle.min.css', 'planet4-editor-style');
    }

    /**
     * Applies filters for list of users in dropdown
     *
     * @param Array|null $args The filter options and values.
     */
    public function filter_authors(?array $args): ?array
    {
        if (isset($args['who'])) {
            $args['role__in'] = ['administrator', 'author', 'campaigner', 'contributor', 'editor'];
            unset($args['who']);
        }
        return $args;
    }

    /**
     * Registers taxonomies.
     */
    public function register_taxonomies(): void
    {
        register_taxonomy_for_object_type('post_tag', 'page');
        register_taxonomy_for_object_type('category', 'page');
    }

    /**
     * Registers oembed provider for Carto map.
     */
    public function register_oembed_provider(): void
    {
        wp_oembed_add_provider(
            '#https?://(?:www\.)?[^/^\.]+\.carto(db)?\.com/\S+#i',
            'https://services.carto.com/oembed',
            true
        );
    }

    /**
     * Restrict creation of tags from all roles besides administrator.
     *
     * @param string $term The term to be added.
     * @param string $taxonomy Taxonomy slug.
     *
     * @return WP_Error|string
     */
    public function disallow_insert_term(string $term, string $taxonomy)
    {
        $user = wp_get_current_user();

        if ('post_tag' === $taxonomy && !in_array('administrator', (array) $user->roles, true)) {
            return new WP_Error(
                'disallow_insert_term',
                __('Your role does not have permission to add terms to this taxonomy', 'planet4-master-theme-backend')
            );
        }

        return $term;
    }

    /**
     * Remove block directory assets and rest endpoint.
     */
    public function disable_block_directory(): void
    {
        remove_action('enqueue_block_editor_assets', 'wp_enqueue_editor_block_directory_assets');
        add_filter('rest_endpoints', [$this, 'disable_block_directory_endpoint']);
    }

    /**
     * Remove block directory endpoint.
     *
     * @param array $endpoints The available endpoints.
     *
     */
    public function disable_block_directory_endpoint(array $endpoints): array
    {
        unset($endpoints['/wp/v2/block-directory/search']);
        return $endpoints;
    }

    /**
     * Set post tags options to retain their order on save and return them ordered
     *
     * @param array  $args     Array of arguments for registering a taxonomy.
     * @param string $taxonomy Taxonomy key.
     *
     * @return array $args Array of arguments for registering a taxonomy.
     */
    public function set_post_tags_as_ordered(array $args, string $taxonomy): array
    {
        if ('post_tag' !== $taxonomy) {
            return $args;
        }

        $args['sort'] = true;
        $args['args'] = ['orderby' => 'term_order'];
        return $args;
    }
}
