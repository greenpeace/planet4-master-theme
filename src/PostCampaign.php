<?php

namespace P4\MasterTheme;

/**
 * Class P4\MasterTheme\PostCampaign
 */
class PostCampaign
{
    /**
     * Post Type
     */
    const POST_TYPE = 'campaign';

    const DEFAULT_NAVBAR_THEME = 'planet4';

    public const META_FIELDS = [
        'p4_campaign_name',
        'theme',
        'campaign_logo',
        'campaign_logo_color',
        'campaign_nav_type',
        'campaign_nav_color',
        'campaign_nav_border',
        'campaign_header_color',
        'campaign_primary_color',
        'campaign_secondary_color',
        'campaign_header_primary',
        'campaign_body_font',
        'campaign_footer_theme',
        'footer_links_color',
        'p4_hide_page_title_checkbox',
        'p4_title',
        'p4_subtitle',
        'p4_description',
        'background_image_id',
        'background_image',
        'p4_og_title',
        'p4_og_description',
        'p4_og_image',
        'p4_og_image_id',
        'p4-seo-canonical-url',
    ];

    public const LEGACY_THEMES = [
        'default',
        'climate',
        'oceans',
        'plastic',
        'forest',
    ];

    /**
     * Taxonomy_Image constructor.
     */
    public function __construct()
    {
        $this->hooks();
    }

    /**
     * Class hooks.
     */
    private function hooks()
    {
        add_action('init', [ $this, 'register_campaigns_cpt' ]);
        add_action('cmb2_admin_init', [ $this, 'register_campaigns_metaboxes' ]);
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ]);
        add_action('cmb2_render_sidebar_link', [ $this, 'cmb2_render_sidebar_link_field_callback' ], 10, 5);
        add_action('cmb2_render_footer_icon_link', [ $this, 'cmb2_render_footer_icon_link_field_callback' ], 10, 5);

        add_filter('get_user_option_edit_campaign_per_page', [ $this, 'set_default_items_per_page' ], 10, 3);

        add_filter(
            'manage_campaign_posts_columns',
            function ($columns) {
                return array_merge($columns, [ 'theme' => __('Theme', 'planet4-master-theme-backend') ]);
            }
        );

        add_action(
            'manage_campaign_posts_custom_column',
            function ($column_key, $post_id) {
                echo esc_html(get_post_meta($post_id, 'theme', true));
            },
            10,
            2
        );
        add_filter(
            'manage_edit-campaign_sortable_columns',
            function ($columns) {
                $columns['theme'] = 'theme';

                return $columns;
            }
        );
        add_action(
            'pre_get_posts',
            function ($query) {
                if (! is_admin()) {
                    return;
                }

                $orderby = $query->get('orderby');

                if ('theme' === $orderby) {
                    $query->set('meta_key', 'theme');
                    $query->set('orderby', 'meta_value');
                }
            }
        );
    }

    /**
     * Increase the maximum number of items displayed so that there are enough items to collapse any child pages.
     *
     * @param int|null $result Possibly value chosen by the current user.
     * @param string   $option The name of the option.
     * @param object   $user The current user.
     *
     * @return int The amount of pages that will be used.
     */
    public function set_default_items_per_page($result, $option, $user)
    {
        if ((int) $result < 1) {
            return 200;
        }

        return $result;
    }

    /**
     * Register campaigns cpt
     */
    public function register_campaigns_cpt()
    {

        $labels = [
            'name' => _x('Campaigns', 'post type general name', 'planet4-master-theme-backend'),
            'singular_name' => _x('Campaign', 'post type singular name', 'planet4-master-theme-backend'),
            'menu_name' => _x('Campaigns', 'admin menu', 'planet4-master-theme-backend'),
            'name_admin_bar' => _x('Campaign', 'add new on admin bar', 'planet4-master-theme-backend'),
            'add_new' => _x('Add New', 'campaign', 'planet4-master-theme-backend'),
            'add_new_item' => __('Add New Campaign', 'planet4-master-theme-backend'),
            'new_item' => __('New Campaign', 'planet4-master-theme-backend'),
            'edit_item' => __('Edit Campaign', 'planet4-master-theme-backend'),
            'view_item' => __('View Campaign', 'planet4-master-theme-backend'),
            'all_items' => __('All Campaigns', 'planet4-master-theme-backend'),
            'search_items' => __('Search Campaigns', 'planet4-master-theme-backend'),
            'parent_item_colon' => __('Parent Campaign:', 'planet4-master-theme-backend'),
            'not_found' => __('No campaigns found.', 'planet4-master-theme-backend'),
            'not_found_in_trash' => __('No campaigns found in Trash.', 'planet4-master-theme-backend'),
        ];

        $args = [
            'labels' => $labels,
            'description' => __('Campaigns', 'planet4-master-theme-backend'),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'query_var' => true,
            'rewrite' => [
                'slug' => 'campaign',
                'with_front' => false,
            ],
            'capability_type' => [ 'campaign', 'campaigns' ],
            'map_meta_cap' => true,
            'has_archive' => true,
            'hierarchical' => true,
            'show_in_nav_menus' => true,
            'menu_position' => null,
            'menu_icon' => 'dashicons-megaphone',
            'show_in_rest' => true,
            'supports' => [
                'page-attributes',
                'title',
                'editor',
                'author',
                'thumbnail',
                'excerpt',
                'revisions',
                // Required to expose meta fields in the REST API.
                'custom-fields',
            ],
        ];

        register_post_type(self::POST_TYPE, $args);

        foreach (self::META_FIELDS as $field) {
            self::campaign_field($field);
        }
    }

    /**
     * Register Color Picker Metabox for navigation
     */
    public function register_campaigns_metaboxes()
    {
        $cmb = new_cmb2_box(
            [
                'id' => 'campaign_nav_settings_mb',
                'title' => __('Page Design', 'planet4-master-theme-backend'),
                'object_types' => [
                    'campaign',
                ],
                'context' => 'normal',
                'priority' => 'high',
                'show_names' => true, // Show field names on the left.
            ]
        );

        $cmb->add_field(
            [
                'id' => 'new_sidebar_link',
                'type' => 'sidebar_link',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Footer item 1', 'planet4-master-theme-backend'),
                'id' => 'campaign_footer_item1',
                'type' => 'footer_icon_link',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Footer item 2', 'planet4-master-theme-backend'),
                'id' => 'campaign_footer_item2',
                'type' => 'footer_icon_link',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Footer item 3', 'planet4-master-theme-backend'),
                'id' => 'campaign_footer_item3',
                'type' => 'footer_icon_link',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Footer item 4', 'planet4-master-theme-backend'),
                'id' => 'campaign_footer_item4',
                'type' => 'footer_icon_link',
            ]
        );

        $cmb->add_field(
            [
                'name' => __('Footer item 5', 'planet4-master-theme-backend'),
                'id' => 'campaign_footer_item5',
                'type' => 'footer_icon_link',
            ]
        );
    }

    /**
     * Load assets.
     */
    public function enqueue_admin_assets()
    {
        wp_register_style(
            'cmb-style',
            get_template_directory_uri() . '/admin/css/campaign.css',
            [],
            Loader::theme_file_ver('admin/css/campaign.css')
        );
        wp_enqueue_style('cmb-style');
    }

    /**
     * CMB2 custom field(sidebar_link) callback function.
     *
     * @param array $field The CMB2 field array.
     * @param array $value The CMB2 field Value.
     * @param array $object_id The id of the object.
     * @param array $object_type The type of object.
     * @param array $field_type Instance of the `cmb2_Meta_Box_types` object.
     */
    public function cmb2_render_sidebar_link_field_callback(
        $field,
        $value,
        $object_id,
        $object_type,
        $field_type
    ) {
        ?>
        <a
            href="#" onclick="openSidebar()"
            id="new_sidebar_link">
            <?php
                esc_html_e('Design settings moved to a new sidebar.', 'planet4-master-theme-backend')
            ?>
        </a>
        <script>
            function openSidebar() {
                let sidebarButton = document.querySelector( '.edit-post-pinned-plugins button[aria-expanded=false]' );
                if ( sidebarButton ) {
                    sidebarButton.click();
                }
            }
        </script>
        <?php
    }

    /**
     * CMB2 custom field(footer_icon_link) callback function.
     *
     * @param array $field The CMB2 field array.
     * @param array $value The CMB2 field Value.
     * @param array $object_id The id of the object.
     * @param array $object_type The type of object.
     * @param array $field_type Instance of the `cmb2_Meta_Box_types` object.
     */
    public function cmb2_render_footer_icon_link_field_callback($field, $value, $object_id, $object_type, $field_type)
    {
        $value = wp_parse_args(
            $value,
            [
                'url' => '',
                'icon' => '',
            ]
        );
        ?>
        <div class="alignleft">
        <?php
            echo wp_kses(
                $field_type->input(
                    [
                        'class' => 'cmb-type-text-medium',
                        'name' => esc_attr($field_type->_name('[url]')),
                        'id' => esc_attr($field_type->_id('_url')),
                        'type' => 'text',
                        'value' => esc_url($value['url']),
                        'placeholder' => __('Footer item link', 'planet4-master-theme-backend'),
                    ]
                ),
                [
                    'input' => [
                        'class' => [],
                        'placeholder' => [],
                        'name' => [],
                        'id' => [],
                        'type' => [],
                        'value' => [],
                        'data-hash' => [],
                    ],
                ]
            );
        ?>
        </div>
        <div class="alignleft">
        <?php
            echo wp_kses(
                $field_type->input(
                    [
                        'class' => 'cmb-type-text-medium',
                        'name' => esc_attr($field_type->_name('[icon]')),
                        'id' => esc_attr($field_type->_id('_icon')),
                        'type' => 'text',
                        'value' => $value['icon'],
                        'placeholder' => __('Footer icon name', 'planet4-master-theme-backend'),
                    ]
                ),
                [
                    'input' => [
                        'class' => [],
                        'placeholder' => [],
                        'name' => [],
                        'id' => [],
                        'type' => [],
                        'value' => [],
                        'data-hash' => [],
                    ],
                ]
            );
        ?>
        </div>
        <div class="alignleft"> <?php esc_html_e('In the “Footer icon name” field add the name of the icon you want from the', 'planet4-master-theme-backend'); ?> <a target="_blank" href="https://github.com/greenpeace/planet4-master-theme/tree/master/assets/src/images/icons"><?php esc_html_e('list of icons in the CSS styleguide', 'planet4-master-theme-backend'); ?></a>. e.g. twitter-square</div>
        <?php
    }

    /**
     * Register a key as a post_meta with the argument `show_in_rest` that is needed on all fields so they can be
     * used through the REST api. Also set `type` and `single` as both are the same for all attributes.
     *
     * @param string $meta_key Identifier the post_meta field will be registered with.
     * @param array  $args Arguments which are passed on to register_post_meta.
     *
     * @return void A description of the field.
     */
    private static function campaign_field(
        string $meta_key,
        array $args = []
    ): void {
        $args = array_merge(
            [
                'show_in_rest' => true,
                'type' => 'string',
                'single' => true,
            ],
            $args
        );
        register_post_meta(self::POST_TYPE, $meta_key, $args);
    }

    /**
     * Gets the default for a field.
     *
     * @param array $field A field from the JSON theme file.
     * @return string Default value
     */
    private static function get_field_default($field)
    {
        if (isset($field['configurations']) && isset($field['configurations']['default'])) {
            $default_configuration = $field['configurations']['default'];

            if (isset($field['configurations'][ $default_configuration ]['default'])) {
                return $field['configurations'][ $default_configuration ]['default'];
            }
        } elseif (isset($field['default'])) {
            return $field['default'];
        }

        return null;
    }

    /**
     * Get the theme defaults
     *
     * @param mixed $theme_json The JSON theme file.
     */
    private static function get_theme_defaults($theme_json)
    {
        $defaults = [];
        foreach ($theme_json['fields'] as $field) {
            $defaults[ $field['id'] ] = self::get_field_default($field);
        }

        return $defaults;
    }

    /**
     * Determine the css variables for a certain post.
     *
     * @param array $meta The meta containing the variable values.
     *
     * @return array The values that will be used for the css variables.
     */
    public static function css_vars(array $meta): array
    {
        $theme = self::get_theme($meta);

        // As a way to make new themes use the same config file, check if removing "-new" gives a legacy theme name.
        $potential_new_version = str_replace('-new', '', $theme);

        $new_theme_json_path = __DIR__ . '/../themes/' . $potential_new_version . '.json';

        if (file_exists($new_theme_json_path)) {
            $new_theme = json_decode(file_get_contents($new_theme_json_path), true, 512, JSON_THROW_ON_ERROR); // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
        } else {
            $themes = json_decode(get_option('planet4_themes', '[]'), true);

            $new_theme = $themes[ $theme ] ?? [];
        }

        if (! in_array($potential_new_version, self::LEGACY_THEMES, true)) {
            // If it doesn't, stop here as all code below handles the config file.
            return $new_theme;
        }
        $theme = $potential_new_version;
        $meta['theme'] = $potential_new_version;

        // TODO: Use wp_safe_remote_get?
        // TODO: Handle errors.
        $theme_json = json_decode(
            // Ignoring the PHPCS error in the next line because it's a local file, not a remote request.
            file_get_contents(__DIR__ . '/../theme_options/' . $theme . '.json'), // phpcs:ignore WordPress.WP.AlternativeFunctions.file_get_contents_file_get_contents
            true
        );

        $defaults = self::get_theme_defaults($theme_json);

        // Use only meta keys that exist in the defaults.
        $intersect = array_intersect_key(array_filter($meta), $defaults);

        // Replace the defaults with the campaign options where applicable.
        $css_vars = array_merge($defaults, $intersect);

        $css_vars = self::get_navbar_theme($css_vars);
        $css_vars = self::get_footer_theme($css_vars);
        $css_vars = self::replace_font_aliases($css_vars);
        $css_vars = self::get_body_font($css_vars, $meta);
        $css_vars = self::migrate_old_vars($css_vars);

        $css_vars = array_filter($css_vars);

        return array_merge($new_theme ?? [], $css_vars);
    }

    /**
     * @param array $css_vars The array containing the CSS variables.
     * @param array $meta The meta containing the style settings.
     * @return string The body font.
     */
    public static function get_body_font($css_vars, $meta): array
    {
        // Temporary fix for old campaigns having "campaign_body_font" as a "campaign".
        if (isset($css_vars['campaign_body_font']) && 'campaign' === $css_vars['campaign_body_font']) {
            $campaigns_font_map = [
                'default' => 'lora',
                'antarctic' => 'sanctuary',
                'arctic' => 'Save the Arctic',
                'climate' => 'Jost',
                'forest' => 'Kanit',
                'oceans' => 'Montserrat',
                'oil' => 'Anton',
                'plastic' => 'Montserrat',
            ];
            $theme = self::get_theme($meta);

            $css_vars['campaign_body_font'] = $campaigns_font_map[ $theme ];
        }

        return $css_vars;
    }

    /**
     * @param array $css_vars The array containing the CSS variables.
     * @return array The variables for the footer theme.
     */
    public static function replace_font_aliases(array $css_vars): array
    {
        // TODO: Remove these special cases.
        if (isset($css_vars['campaign_header_primary'])) {
            $css_vars['campaign_header_primary'] = str_replace('Montserrat_Light', 'Montserrat', $css_vars['campaign_header_primary']);
        }

        if (isset($css_vars['campaign_body_font'])) {
            $css_vars['campaign_body_font'] = str_replace('Montserrat_Light', 'Montserrat', $css_vars['campaign_body_font']);
        }

        return $css_vars;
    }

    /**
     * Migrate variables that were changed or split into multiple. Preserve original variables too so we can already
     * merge.
     *
     * @param array $css_vars CSS variables lacking newer properties.
     *
     * @return array Migrated CSS variables.
     */
    private static function migrate_old_vars(array $css_vars): array
    {
        $mappings = [
            'footer_links_color' => [
                '--site-footer--a--color',
                '--site-footer--a--hover--color',
                '--site-footer--color',
                '--site-footer--icon--color',
                '--site-footer--icon--hover--color',
                '--site-footer--copyright--color',
                '--site-footer--copyright--a--color',
                '--site-footer--copyright--a--hover--color',
                '--site-footer--copyright--icon--color',
            ],
            'campaign_header_primary' => [ 'headings--font-family' ],
            'campaign_body_font' => [ 'body--font-family' ],
            'campaign_nav_color' => [ 'top-navigation--background' ],
        ];
        foreach ($mappings as $from => $to) {
            if (empty($css_vars[ $from ])) {
                continue;
            }
            foreach ($to as $new_name) {
                // Both with and without '--' works, but the version with dashes might exist in the new themes.
                // If we wouldn't add them here, it would result in setting the same property twice. The last one "wins"
                // however Chrome got really confused by this, pointing to the first one, instead of the last one which
                // is used.
                $css_vars[ '--' . $new_name ] = $css_vars[ $from ];
            }
        }

        return $css_vars;
    }

    /**
     * Get the navigation bar variables based on the meta settings.
     *
     * @param array $css_vars The mix of meta fields and defaults.
     *
     * @return array The variables for the navigation bar.
     */
    public static function get_navbar_theme(array $css_vars): array
    {
        if (self::DEFAULT_NAVBAR_THEME === $css_vars['campaign_nav_type']) {
            $css_vars['campaign_logo_color'] = null;
            $css_vars['campaign_nav_color'] = null;
            $css_vars['campaign_logo'] = null;
        }

        return $css_vars;
    }

    /**
     * Get the footer variables based on the meta settings.
     *
     * @param array $css_vars The array containing the CSS variables.
     *
     * @return array The variables for the footer theme.
     */
    public static function get_footer_theme(array $css_vars): array
    {
        $footer_theme = ! empty($css_vars['campaign_footer_theme'])
                                            ? $css_vars['campaign_footer_theme']
                                            : null;

        $default_footer_links_color = $css_vars['campaign_nav_color'] ? $css_vars['campaign_nav_color'] : '#1A1A1A';

        if ('white' === $footer_theme) {
            $css_vars['footer_links_color'] = $css_vars['footer_links_color'] ? $css_vars['footer_links_color'] : $default_footer_links_color;
            $css_vars['--site-footer--background'] = '#FFFFFF';
            $css_vars['--site-footer--copyright--background'] = '#FFFFFF';
        } elseif (self::DEFAULT_NAVBAR_THEME === $css_vars['campaign_nav_type']) {
            $css_vars['footer_links_color'] = null;
            $css_vars['--site-footer--background'] = null;
        } else {
            switch (( $css_vars['campaign_logo_color'] ?? null )) {
                case 'dark':
                    $css_vars['footer_links_color'] = '#1A1A1A';
                    break;
                case 'green':
                    $css_vars['footer_links_color'] = '#FFFFFF';
                    break;
                default:
                    $css_vars['footer_links_color'] = '#FFFFFF';
            }
            $css_vars['--site-footer--background'] = $css_vars['campaign_nav_color'];
            $css_vars['--site-footer--copyright--background'] = $css_vars['campaign_nav_color'];
        }

        return $css_vars;
    }

    /**
     * Get the theme based on the meta settings.
     *
     * @param array $meta The meta containing the style settings.
     *
     * @return string The identifier of the theme.
     */
    public static function get_theme(array $meta): string
    {
        $theme = $meta['theme'] ?? $meta['_campaign_page_template'] ?? null;

        return $theme ? $theme : 'default';
    }

    /**
     * Get the logo based on the meta settings. Ensures that no other campaign logo will be used even if that's the value stored.
     *
     * @param array $meta The meta containing the style settings.
     *
     * @return string The identifier of the logo.
     */
    public static function get_logo(array $meta): string
    {
        $logo = $meta['campaign_logo'] ?? null;
        if (! $logo) {
            return 'greenpeace';
        }

        $theme = self::get_theme($meta);

        if ('default' !== $theme) {
            return 'greenpeace' === $logo ? 'greenpeace' : str_replace('-new', '', $theme);
        }

        return $logo ? $logo : 'greenpeace';
    }
}
