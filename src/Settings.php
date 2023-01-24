<?php

namespace P4\MasterTheme;

use CMB2_Field;
use P4\MasterTheme\Settings\Comments;
use P4\MasterTheme\Settings\Features;
use P4\MasterTheme\Settings\InformationArchitecture as IA;

/**
 * Class P4\MasterTheme\Settings
 */
class Settings
{
    /**
     * ID of the Metabox
     */
    public const METABOX_ID = 'option_metabox';

    /**
     * Option key
     *
     */
    public const KEY = 'planet4_options';

    public const SLACK_WEBHOOK = 'slack_webhook';

    /**
     * Option page slug
     *
     */
    private string $slug = 'planet4_settings_navigation';

    /**
     * Options Page title
     *
     */
    protected string $title = '';

    /**
     * Options Page hook
     *
     */
    protected string $options_page = '';

    /**
     * Subpages
     *
     * @var array
     * Includes arrays with the title and fields of each subpage
     */
    protected array $subpages = [];

    /**
     * Constructor
     */
    public function __construct()
    {

        // Set our title.
        $this->title = __('Planet 4', 'planet4-master-theme-backend');

        // Set our subpages.
        // Each subpage has a title and a path and the fields.
        $this->subpages = [
            'planet4_settings_navigation' => [
                'title' => 'Navigation',
                'fields' => [
                    [
                        'name' => __('NRO Selector Title', 'planet4-master-theme-backend'),
                        'id' => 'website_navigation_title',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Select Act Page', 'planet4-master-theme-backend'),
                        'id' => 'act_page',
                        'type' => 'act_page_dropdown',
                    ],

                    [
                        'name' => __('Select Explore Page', 'planet4-master-theme-backend'),
                        'id' => 'explore_page',
                        'type' => 'explore_page_dropdown',
                    ],

                    [
                        'name' => __('Select Issues Parent Category', 'planet4-master-theme-backend'),
                        'id' => 'issues_parent_category',
                        'type' => 'category_select_taxonomy',
                    ],
                    [
                        'name' => __('Website Navigation Style', 'planet4-master-theme-backend'),
                        'desc' => __('Select a style', 'planet4-master-theme-backend'),
                        'id' => 'website_navigation_style',
                        'type' => 'select',
                        'default' => 'dark',
                        'options' => [
                            'dark' => __('Dark', 'planet4-master-theme-backend'),
                            'light' => __('Light', 'planet4-master-theme-backend'),
                        ],
                    ],
                ],
            ],
            'planet4_settings_donate_button' => [
                'title' => 'Donate button',
                'fields' => [
                    [
                        'name' => __('Donate button link', 'planet4-master-theme-backend'),
                        'id' => 'donate_button',
                        'type' => 'text',
                        'attributes' => [
                            'type' => 'text',
                        ],
                    ],

                    [
                        'name' => __('Donate button text', 'planet4-master-theme-backend'),
                        'id' => 'donate_text',
                        'type' => 'text',
                        'attributes' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
            'planet4_settings_defaults_content' => [
                'title' => 'Defaults content',
                'fields' => [
                    [
                        'name' => __('Default P4 Post Type', 'planet4-master-theme-backend'),
                        'id' => 'default_p4_pagetype',
                        'type' => 'pagetype_select_taxonomy',
                    ],

                    [
                        'name' => __('Take Action Covers default button text', 'planet4-master-theme-backend'),
                        'id' => 'take_action_covers_button_text',
                        'type' => 'text',
                        'attributes' => [
                            'type' => 'text',
                        ],
                        'desc' => __('Add default button text which appears on <b>Take Action</b> card of <b>Take Action Covers</b> block. <br>Also it would be used for Take Action Cards inside Posts and Take Action Cards in search results', 'planet4-master-theme-backend'),
                    ],

                    [
                        'name' => __('Reading time: words per minute', 'planet4-master-theme-backend'),
                        'id' => 'reading_time_wpm',
                        'type' => 'text',
                        'attributes' => [
                            'type' => 'number',
                        ],
                        'default' => Post\ReadingTimeCalculator::DEFAULT_WPM,
                        'desc' => __('Average reading words per minute (usually between 220 and 320).', 'planet4-master-theme-backend'),
                    ],

                    // HappyPoint settings.
                    [
                        'name' => __('Default Happy Point Background Image', 'planet4-master-theme-backend'),
                        'id' => 'happy_point_bg_image',
                        'type' => 'file',
                        'options' => [
                            'url' => false,
                        ],
                        'text' => [
                            'add_upload_file_text' => __('Add Default Happy Point Background Image', 'planet4-master-theme-backend'),
                        ],
                        'query_args' => [
                            'type' => 'image',
                        ],
                        'desc' => __('Minimum image width should be 1920px', 'planet4-master-theme-backend'),
                    ],

                    [
                        'name' => __('Default Happy Point Form', 'planet4-master-theme-backend'),
                        'id' => 'happy_point_content_provider',
                        'type' => 'radio',
                        'options' => [
                            'iframe_url' => __('URL (for iframe)', 'planet4-master-theme-backend'),
                            'embed_code' => __('Embed code (HubSpot)', 'planet4-master-theme-backend'),
                        ],
                        'default' => 'iframe_url',
                    ],

                    [
                        'name' => __('Happy point Subscribe Form URL', 'planet4-master-theme-backend'),
                        'id' => 'engaging_network_form_id',
                        'type' => 'text',
                    ],

                    [
                        'name' => __('Happy Point HubSpot embed code', 'planet4-master-theme-backend'),
                        'id' => 'happy_point_embed_code',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 10,
                            'media_buttons' => false,
                        ],
                    ],
                ],
            ],
            'planet4_settings_search_content' => [
                'title' => 'Search content',
                'fields' => [
                    [
                        'name' => __('Include archived content in search for', 'planet4-master-theme-backend'),
                        'desc' => __(
                            '<b>Important:</b> On change of Include archive content setting, Please kindly',
                            'planet4-master-theme-backend'
                        ) . ' <a href="admin.php?page=elasticpress&do_sync" target="_blank">Sync Elasticsearch</a>.',
                        'id' => 'include_archive_content_for',
                        'type' => 'select',
                        'default' => 'nobody',
                        'options' => [
                            'nobody' => __('Nobody', 'planet4-master-theme-backend'),
                            'logged_in' => __('Logged in users', 'planet4-master-theme-backend'),
                            'all' => __('All users', 'planet4-master-theme-backend'),
                        ],
                    ],
                    [
                        'name' => __('Search content decay', 'planet4-master-theme-backend'),
                        'desc' => __('Amount of lowering of the relevancy score for older results. Between 0 and 1. The lower this number is, the lower older content will be ranked. See image. <br>We use the exponential function (exp, green curve).<br/> <img style="max-width:350px" alt="ElasticSearch decay function graph" src="https://www.elastic.co/guide/en/elasticsearch/reference/current/images/decay_2d.png">', 'planet4-master-theme-backend'),
                        'id' => 'epwr_decay',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Search content decay scale', 'planet4-master-theme-backend'),
                        'desc' => __('Timescale for lowering the relevance of older results. See image above.', 'planet4-master-theme-backend'),
                        'id' => 'epwr_scale',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Search content decay offset', 'planet4-master-theme-backend'),
                        'desc' => __('How old should a post be before relevance is lowered. See image above.', 'planet4-master-theme-backend'),
                        'id' => 'epwr_offset',
                        'type' => 'text',
                    ],
                ],
            ],
            'planet4_settings_cookies_text' => [
                'title' => 'Cookies',
                'fields' => [
                    [
                        'name' => __('Cookies Box General Text', 'planet4-master-theme-backend'),
                        'id' => 'cookies_field',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 5,
                            'media_buttons' => false,
                        ],
                    ],
                    [
                        'name' => __('Necessary Cookies label', 'planet4-master-theme-backend'),
                        'id' => 'necessary_cookies_name',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Necessary Cookies description', 'planet4-master-theme-backend'),
                        'id' => 'necessary_cookies_description',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 2,
                            'media_buttons' => false,
                            'quicktags' => [
                                'buttons' => 'strong,em',
                            ],
                        ],
                    ],
                    [
                        'name' => __('Analytical Cookies label', 'planet4-master-theme-backend'),
                        'id' => 'analytical_cookies_name',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Analytical Cookies description', 'planet4-master-theme-backend'),
                        'id' => 'analytical_cookies_description',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 2,
                            'media_buttons' => false,
                            'quicktags' => [
                                'buttons' => 'strong,em',
                            ],
                        ],
                    ],
                    [
                        'name' => __('Marketing Cookies label', 'planet4-master-theme-backend'),
                        'id' => 'all_cookies_name',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Marketing Cookies description', 'planet4-master-theme-backend'),
                        'id' => 'all_cookies_description',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 2,
                            'media_buttons' => false,
                            'quicktags' => [
                                'buttons' => 'strong,em',
                            ],
                        ],
                    ],

                    [
                        'name' => __('Enforce Cookies Policy', 'planet4-master-theme-backend'),
                        'desc' => __(
                            'GDPR related setting. By enabling this option specific content will be blocked and will require user consent to be shown.<br>
							<b>WARNING:</b> If the setting is checked this will prevent Google Tag Manager container from being fired unless the user has accepted the cookies policy (giving consent in the cookies bar).<br>
							This might affect the data collected with Google Analytics. For more information please see the documentation in the  <a href="https://planet4.greenpeace.org/handbook/block-cookies/">Planet 4 Handbook</a>.',
                            'planet4-master-theme-backend'
                        ),
                        'id' => 'enforce_cookies_policy',
                        'type' => 'checkbox',
                    ],

                    [
                        'name' => __('Enable Analytical Cookies', 'planet4-master-theme-backend'),
                        'desc' => __('Enable the Analytical cookies option in Cookies block and box', 'planet4-master-theme-backend'),
                        'id' => 'enable_analytical_cookies',
                        'type' => 'checkbox',
                    ],

                    [
                        'name' => __('Reject all cookies', 'planet4-master-theme-backend'),
                        'desc' => __('Add the "Reject all" option in the Cookies box', 'planet4-master-theme-backend'),
                        'id' => 'enable_reject_all_cookies',
                        'type' => 'checkbox',
                    ],
                ],
            ],
            'planet4_settings_copyright' => [
                'title' => 'Copyright',
                'fields' => [
                    [
                        'name' => __('Copyright Text Line 1', 'planet4-master-theme-backend'),
                        'id' => 'copyright_line1',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 3,
                            'media_buttons' => false,
                        ],
                    ],

                    [
                        'name' => __('Copyright Text Line 2', 'planet4-master-theme-backend'),
                        'id' => 'copyright_line2',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 2,
                            'media_buttons' => false,
                        ],
                    ],
                ],
            ],
            'planet4_settings_social' => [
                'title' => 'Social',
                'fields' => [
                    [
                        'name' => __('Facebook Page ID', 'planet4-master-theme-backend'),
                        'id' => 'facebook_page_id',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Facebook App Access Token', 'planet4-master-theme-backend'),
                        'id' => 'fb_app_access_token',
                        'type' => 'text',
                        'desc' => __('FB App Access Token is used to fetch FB & IG oembed in Social Media block.', 'planet4-master-theme-backend') . '<a href="">' . __('Read more', 'planet4-master-theme-backend') . '</a><Br>' . __('Alternatively you could also add FB App ID and App Secret in place of App Access Token.', 'planet4-master-theme-backend') . '<BR>eg. {your-app_id}|{your-app_secret}',
                    ],
                    [
                        'name' => __('Preconnect Domains', 'planet4-master-theme-backend'),
                        'desc' => __('Add a list of third-party URLs to "preconnect" (e.g.: https://in.hotjar.com). Look for "preconnect" in the P4 Handbook for details.', 'planet4-master-theme-backend'),
                        'id' => 'preconnect_domains',
                        'type' => 'textarea',
                        'attributes' => [
                            'type' => 'text',
                        ],
                    ],
                ],
            ],
            'planet4_settings_404_page' => [
                'title' => '404 Page',
                'fields' => [
                    [
                        'name' => __('404 Background Image', 'planet4-master-theme-backend'),
                        'id' => '404_page_bg_image',
                        'type' => 'file',
                        'options' => [
                            'url' => false,
                        ],
                        'text' => [
                            'add_upload_file_text' => __('Add 404 Page Background Image', 'planet4-master-theme-backend'),
                        ],
                        'query_args' => [
                            'type' => 'image',
                        ],
                        'desc' => __('Minimum image width should be 1920px', 'planet4-master-theme-backend'),
                    ],

                    [
                        'name' => __('404 Page text', 'planet4-master-theme-backend'),
                        'id' => '404_page_text',
                        'type' => 'wysiwyg',
                        'options' => [
                            'textarea_rows' => 3,
                            'media_buttons' => false,
                        ],
                        'desc' => __('Add 404 page text', 'planet4-master-theme-backend'),
                    ],
                ],
            ],
            'planet4_settings_campaigns' => [
                'title' => 'Campaigns',
                'fields' => [
                    [
                        'name' => __('Exclude campaign styles when importing', 'planet4-master-theme-backend'),
                        'desc' => __(
                            'Whether to exclude campaign theme and style settings when importing a campaign.',
                            'planet4-master-theme-backend'
                        ),
                        'id' => 'campaigns_import_exclude_style',
                        'type' => 'checkbox',
                    ],
                ],
            ],
            'planet4_settings_analytics' => [
                'title' => 'Analytics',
                'fields' => [
                    [
                        'name' => __('Google Tag Manager Container', 'planet4-master-theme-backend'),
                        'id' => 'google_tag_manager_identifier',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('AB Hide Selector', 'planet4-master-theme-backend'),
                        'desc' => __(
                            'When running an AB test it is possible that the original is shown for a short while (called flicker). If this happens you can enter a CSS selector here and matching elements will be hidden until the test is fully loaded.',
                            'planet4-master-theme-backend'
                        ),
                        'id' => 'ab_hide_selector',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Local Projects Google Sheet ID', 'planet4-master-theme-backend'),
                        'desc' => __('The Google Sheet that is used to get analytics values from local(NRO) spreadsheet.', 'planet4-master-theme-backend'),
                        'id' => 'analytics_local_google_sheet_id',
                        'type' => 'text',
                    ],
                    [
                        'name' => __('Enable Google Consent Mode', 'planet4-master-theme-backend'),
                        'desc' => __("Enabling the Consent Mode will affect your setup in Google Tag Manager. The Consent Mode will prevent tags with built-in consent checks (eg. Google Analytics) from running before the user's consent is granted.", 'planet4-master-theme-backend'),
                        'id' => 'enable_google_consent_mode',
                        'type' => 'checkbox',
                    ],
                ],
            ],
            'planet4_settings_comments' => Comments::get_options_page(),
            'planet4_settings_features' => Features::get_options_page(),
            'planet4_settings_ia' => IA::get_options_page(),
            'planet4_settings_notifications' => [
                'title' => 'Notifications',
                'fields' => [
                    [
                        'name' => __('Slack webhook URL', 'planet4-master-theme-backend'),
                        'desc' => __(
                            '(Coming Soon) The webhook of the Slack channel to send notifications to.',
                            'planet4-master-theme-backend'
                        ),
                        'id' => self::SLACK_WEBHOOK,
                        'type' => 'text',
                    ],
                ],
            ],
        ];

        $this->hooks();
    }

    /**
     * Initiate our hooks
     */
    public function hooks(): void
    {
        add_action('admin_init', [ $this, 'init' ]);
        add_action('admin_menu', [ $this, 'add_options_pages' ]);
        add_action('cmb2_save_options-page_fields_' . self::METABOX_ID, [ $this, 'add_notifications' ]);
        add_filter('cmb2_render_act_page_dropdown', [ $this, 'p4_render_act_page_dropdown' ], 10, 2);
        add_filter('cmb2_render_explore_page_dropdown', [ $this, 'p4_render_explore_page_dropdown' ], 10, 2);
        add_filter('cmb2_render_category_select_taxonomy', [ $this, 'p4_render_category_dropdown' ], 10, 2);
        add_filter('cmb2_render_pagetype_select_taxonomy', [ $this, 'p4_render_pagetype_dropdown' ], 10, 2);
        add_action('admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ]);

        // Make settings multilingual if wpml plugin is installed and activated.
        if (function_exists('is_plugin_active') && is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            add_action('init', [ $this, 'make_settings_multilingual' ]);
        }

        Features::hooks();
    }

    /**
     * Register our setting to WP.
     */
    public function init(): void
    {
        register_setting(self::KEY, self::KEY);
    }

    /**
     * Add menu options page.
     */
    public function add_options_pages(): void
    {
        $this->options_page = add_menu_page($this->title, $this->title, 'manage_options', $this->slug, function (): void {
        }, 'dashicons-admin-site-alt');
        foreach ($this->subpages as $path => $subpage) {
            add_submenu_page(
                $this->slug,
                $subpage['title'],
                $subpage['title'],
                'manage_options',
                $path,
                function () use ($path): void {
                    $this->admin_page_display($path);
                }
            );
        }
    }

    /**
     * Display notifications of success and error
     * This is the method used by WordPress to add a success notification
     *
     * @see https://github.com/WordPress/WordPress/blob/57fb3c6cf016678ab38d7a636b8df41fa2d955f1/wp-admin/options.php#L313
     */
    public function add_notifications(): void
    {
        if (! count(get_settings_errors())) {
            add_settings_error('general', 'settings_updated', __('Settings saved.', 'planet4-master-theme-backend'), 'success');
        }

        settings_errors();
    }

    /**
     * Render act page dropdown.
     *
     * @param CMB2_Field $field_args Field arguments.
     * @param mixed $value Value.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_filter callback
     */
    public function p4_render_act_page_dropdown(CMB2_Field $field_args, $value): void
    {
        wp_dropdown_pages(
            [
                'show_option_none' => esc_html__('Select Page', 'planet4-master-theme-backend'),
                'hide_empty' => 0,
                'hierarchical' => true,
                'selected' => esc_attr($value),
                'name' => 'act_page',
            ]
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Render explore page dropdown.
     *
     * @param CMB2_Field $field_args Field arguments.
     * @param mixed $value Value.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_filter callback
     */
    public function p4_render_explore_page_dropdown(CMB2_Field $field_args, $value): void
    {
        wp_dropdown_pages(
            [
                'show_option_none' => esc_html__('Select Page', 'planet4-master-theme-backend'),
                'hide_empty' => 0,
                'hierarchical' => true,
                'selected' => esc_attr($value),
                'name' => 'explore_page',
            ]
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Render category dropdown.
     *
     * @param CMB2_Field $field_args Field arguments.
     * @param mixed $value Value.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_filter callback
     */
    public function p4_render_category_dropdown(CMB2_Field $field_args, $value): void
    {

        wp_dropdown_categories(
            [
                'show_option_none' => __('Select Category', 'planet4-master-theme-backend'),
                'hide_empty' => 0,
                'hierarchical' => true,
                'orderby' => 'name',
                'selected' => $value,
                'name' => 'issues_parent_category',
            ]
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Render p4-pagetype dropdown.
     *
     * @param CMB2_Field $field_args CMB2 field Object.
     * @param mixed $value Pagetype taxonomy ID.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- add_filter callback
     */
    public function p4_render_pagetype_dropdown(CMB2_Field $field_args, $value): void
    {

        wp_dropdown_categories(
            [
                'show_option_none' => __('Select Posttype', 'planet4-master-theme-backend'),
                'hide_empty' => 0,
                'orderby' => 'name',
                'selected' => $value,
                'name' => 'default_p4_pagetype',
                'taxonomy' => 'p4-page-type',
            ]
        );
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter

    /**
     * Admin page markup. Mostly handled by CMB2.
     *
     * @param string $plugin_page The key for the current page.
     */
    public function admin_page_display(string $plugin_page): void
    {
        $page_config = $this->subpages[ $plugin_page ];

        if ('planet4_settings_donate_button' === $plugin_page) {
            $message = trim($this->show_donate_menu_notice());

            if (empty($message)) {
                return;
            }

            echo '<div class="notice notice-info is-dismissible">'
                . wp_kses_post($message)
                . '</div>';
        }

        $fields = $page_config['fields'];
        $description = $page_config['description'] ?? null;
        // Allow storing options in a different database record.
        $root_option = $page_config['root_option'] ?? self::KEY;

        $add_scripts = $this->subpages[ $plugin_page ]['add_scripts'] ?? null;
        if (is_callable($add_scripts)) {
            $add_scripts();
        }

        $form = cmb2_metabox_form(
            $this->option_metabox($fields, $root_option),
            $root_option,
            [ 'echo' => false ]
        );

        echo sprintf(
            '<div class="wrap %s">
				<h2>%s</h2>
				%s
				%s
			</div>',
            esc_attr(self::KEY),
            esc_html(get_admin_page_title()),
            wp_kses($description ? '<div>' . $description . '</div>' : '', 'post'),
            $form // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        );
    }

    /**
     * Show notice to inform users that the Donate button settings are now in Appearance > Menus.
     *
     */
    private function show_donate_menu_notice(): string
    {
        return '<p>These settings have been moved to <a href="/wp-admin/nav-menus.php?action=locations">a new menu location</a>
			in Appearance > Menus, specifically the "Donate Button" location.</br>
			There you can add a custom link with your text and link for the Donate button,
			and you will also be able to define dropdown menu items if you want to.</p>';
    }

    /**
     * Defines the theme option metabox and field configuration.
     *
     * @param array  $fields expects the fields (if they exist) of this subpage.
     * @param string $option_id Key of option to store serialized array in.
     *
     * @return array
     */
    public function option_metabox(array $fields, string $option_id): array
    {
        return [
            'id' => self::METABOX_ID,
            'show_on' => [
                'key' => 'options-page',
                'value' => [
                    $option_id,
                ],
            ],
            'show_names' => true,
            'fields' => $fields,
        ];
    }

    /**
     * Hook for wpml plugin.
     * Enables the possibility to save a different value per language for the theme options using WPML language switcher.
     */
    public function make_settings_multilingual(): void
    {
        do_action('wpml_multilingual_options', 'planet4_options');
    }

    /**
     * Loads options assets.
     */
    public function enqueue_admin_assets(): void
    {
        wp_register_style(
            'options-style',
            get_template_directory_uri() . '/admin/css/options.css',
            [],
            Loader::theme_file_ver('admin/css/options.css')
        );
        wp_enqueue_style('options-style');
    }
}
