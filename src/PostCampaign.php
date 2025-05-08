<?php

namespace P4\MasterTheme;

use CMB2_Field;
use CMB2_Types;

/**
 * Class P4\MasterTheme\PostCampaign
 */
class PostCampaign
{
    /**
     * Post Type
     */
    public const POST_TYPE = 'campaign';

    public const DEFAULT_NAVBAR_THEME = 'planet4';

    public const META_FIELDS = [
        'p4_campaign_name',
        'p4_local_project',
        'p4_basket_name',
        'p4_department',
        'campaign_nav_type',
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
    private function hooks(): void
    {
        add_action('init', [ $this, 'register_campaigns_cpt' ]);
        add_action('cmb2_admin_init', [ $this, 'register_campaigns_metaboxes' ]);
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ]);
        add_action('cmb2_render_footer_icon_link', [ $this, 'cmb2_render_footer_icon_link_field_callback' ], 10, 5);

        add_filter('get_user_option_edit_campaign_per_page', [ $this, 'set_default_items_per_page' ], 10, 1);

        add_filter(
            'manage_campaign_posts_columns',
            function ($columns) {
                return array_merge($columns, [ 'theme' => __('Theme', 'planet4-master-theme-backend') ]);
            }
        );

        add_action(
            'manage_campaign_posts_custom_column',
            // phpcs:ignore SlevomatCodingStandard.Functions.UnusedParameter -- add_action callback
            function ($column_key, $post_id): void {
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
            function ($query): void {
                if (! is_admin()) {
                    return;
                }

                $orderby = $query->get('orderby');

                if ('theme' !== $orderby) {
                    return;
                }

                $query->set('meta_key', 'theme');
                $query->set('orderby', 'meta_value');
            }
        );
    }

    /**
     * Increase the maximum number of items displayed so that there are enough items to collapse any child pages.
     *
     * @param int|null $result Possibly value chosen by the current user.
     *
     * @return int The amount of pages that will be used.
     */
    public function set_default_items_per_page(?int $result): int
    {
        if ((int) $result < 1) {
            return 200;
        }

        return $result;
    }

    /**
     * Register campaigns cpt
     */
    public function register_campaigns_cpt(): void
    {

        $labels = [
            'name' => _x('Campaigns', 'post type general name', 'planet4-master-theme-backend'),
            'singular_name' => _x('Campaign', 'post type singular name', 'planet4-master-theme-backend'),
            'menu_name' => _x('Campaigns', 'admin menu', 'planet4-master-theme-backend'),
            'name_admin_bar' => _x('Campaign', 'add new on admin bar', 'planet4-master-theme-backend'),
            'add_new' => _x('Add New', 'campaign', 'planet4-master-theme-backend'),
            'add_new_item' => __('Add Campaign', 'planet4-master-theme-backend'),
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
    public function register_campaigns_metaboxes(): void
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
    public function enqueue_admin_assets(): void
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
     * CMB2 custom field(footer_icon_link) callback function.
     *
     * @param CMB2_Field $field The CMB2 field array.
     * @param mixed $value The CMB2 field Value.
     * @param int $object_id The id of the object.
     * @param string $object_type The type of object.
     * @param CMB2_Types $field_type Instance of the `cmb2_Meta_Box_types` object.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_action callback
     * phpcs:disable Generic.Files.LineLength.MaxExceeded
     */
    public function cmb2_render_footer_icon_link_field_callback(
        CMB2_Field $field,
        $value,
        int $object_id,
        string $object_type,
        CMB2_Types $field_type
    ): void {
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
        <div class="alignleft"><?php esc_html_e('In the “Footer icon name” field add the name of the icon you want from the', 'planet4-master-theme-backend'); ?> <a target="_blank" href="https://github.com/greenpeace/planet4-master-theme/tree/master/assets/src/images/icons"><?php esc_html_e('list of icons in the CSS styleguide', 'planet4-master-theme-backend'); ?></a>. e.g. twitter-square</div>
        <?php
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
    // phpcs:enable Generic.Files.LineLength.MaxExceeded

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
}
