<?php

namespace P4\MasterTheme;

use Timber\Timber;

/**
 * Class Context Sets common context fields.
 */
class Context
{
    /**
     * Site information from the Master Site class.
     *
     */
    protected static object $master_site_context;

    /**
     * Class hooks.
     *
     */
    public static function hooks(object $context): void
    {
        self::$master_site_context = $context;

        add_filter('timber/context', [self::class, 'add_to_context']);
    }

    /**
     * Adds more data to the context variable that will be passed to the main template.
     *
     * @param array $context The associative array with data to be passed to the main template.
     *
     * @return mixed
     */
    public static function add_to_context(array $context)
    {
        global $wp;

        $context['cookies'] = [
            'text' => planet4_get_option('cookies_field'),
            'enable_analytical_cookies' => planet4_get_option('enable_analytical_cookies'),
            'enable_reject_all_cookies' => planet4_get_option('enable_reject_all_cookies'),
            'enable_google_consent_mode' => planet4_get_option('enable_google_consent_mode'),
            'settings_copy' => [
                'necessary_cookies_name' => planet4_get_option('necessary_cookies_name', ''),
                'necessary_cookies_description' => planet4_get_option('necessary_cookies_description', ''),
                'analytical_cookies_name' => planet4_get_option('analytical_cookies_name', ''),
                'analytical_cookies_description' => planet4_get_option('analytical_cookies_description', ''),
                'all_cookies_name' => planet4_get_option('all_cookies_name', ''),
                'all_cookies_description' => planet4_get_option('all_cookies_description', ''),
            ],
        ];
        $context['theme_uri'] = get_template_directory_uri();
        $context['data_nav_bar'] = [
            'images' => get_template_directory_uri() . '/images/',
            'home_url' => home_url('/'),
            'search_query' => trim(get_search_query()),
            'country_dropdown_toggle' => __('Toggle worldwide site selection menu', 'planet4-master-theme'),
            'navbar_search_toggle' => __('Toggle search box', 'planet4-master-theme'),
        ];
        $context['domain'] = 'planet4-master-theme';
        $context['foo'] = 'bar'; // For unit test purposes.

        if (has_nav_menu('navigation-bar-menu')) {
            $menu = Timber::get_menu('navigation-bar-menu');
            $menu_items = $menu->get_items();
            $context['navbar_menu'] = $menu;
            $context['navbar_menu_items'] = array_filter(
                $menu_items,
                function ($item) {
                    return !in_array('wpml-ls-item', $item->classes ?? [], true);
                }
            );
        }

        // Check if the menu has been created.
        if (has_nav_menu('donate-menu')) {
            $donate_menu = Timber::get_menu('donate-menu');

            // Check if it has at least 1 item added into the menu.
            if (!empty($donate_menu->get_items())) {
                $context['donate_menu_items'] = $donate_menu->get_items();
            }
        }


        $languages = function_exists('icl_get_languages') ? icl_get_languages() : [];

        $context['site_languages'] = $languages;
        $context['languages'] = count($languages); // Keep this variable name as long as NRO themes use it.

        $context['site'] = self::$master_site_context;
        $context['current_url'] = trailingslashit(home_url($wp->request));
        $context['sort_options'] = [
            '_score' => [
                'name' => 'Most relevant',
                'order' => 'DESC',
            ],
            'post_date' => [
                'name' => 'Newest',
                'order' => 'DESC',
            ],
            'post_date_asc' => [
                'name' => 'Oldest',
                'order' => 'ASC',
            ],
        ];
        $context['default_sort'] = Search\SearchPage::DEFAULT_SORT;

        $options = get_option('planet4_options');

        // Do not embed google tag manager js if 'greenpeace' cookie is not set
        // or enforce_cookies_policy setting is not enabled.
        $context['enforce_cookies_policy'] = isset($options['enforce_cookies_policy']) ? true : false;
        $context['google_tag_value'] = $options['google_tag_manager_identifier'] ?? '';
        $context['google_tag_domain'] = !empty($options['google_tag_manager_domain']) ?
            $options['google_tag_manager_domain'] : 'www.googletagmanager.com';
        $context['consent_default_analytics_storage'] =
            planet4_get_option('consent_default_analytics_storage') ?? 'denied';
        $context['consent_default_ad_storage'] =
            planet4_get_option('consent_default_ad_storage') ?? 'denied';
        $context['consent_default_ad_user_data'] =
            planet4_get_option('consent_default_ad_user_data') ?? 'denied';
        $context['consent_default_ad_personalization'] =
            planet4_get_option('consent_default_ad_personalization') ?? 'denied';
        $context['consent_default_url_passthrough'] =
            planet4_get_option('consent_default_url_passthrough') ?? false;
        $context['facebook_page_id'] = $options['facebook_page_id'] ?? '';
        $context['preconnect_domains'] = [];
        $context['vwo_account_id'] = $options['vwo_account_id'] ?? null;

        if (!empty($options['preconnect_domains'])) {
            $preconnect_domains = explode("\n", $options['preconnect_domains']);
            $preconnect_domains = array_map('trim', $preconnect_domains);
            $preconnect_domains = array_filter($preconnect_domains);

            $context['preconnect_domains'] = $preconnect_domains;
        }

        // hreflang metadata.
        if (is_front_page()) {
            $context['hreflang'] = self::generate_hreflang_meta();
        }

        // Datalayer feed.
        $current_user = wp_get_current_user();
        if ($current_user->ID) {
            $context['p4_signedin_status'] = 'true';
            $context['p4_visitor_type'] = $current_user->roles[0] ?? '';
        } else {
            $context['p4_signedin_status'] = 'false';
            $context['p4_visitor_type'] = 'guest';
        }

        $context['website_navbar_title'] = $options['website_navigation_title']
            ?? __('International (English)', 'planet4-master-theme');

        $context['act_page_id'] = $options['act_page'] ?? '';
        $context['explore_page_id'] = $options['explore_page'] ?? '';

        // Footer context.
        $context['copyright_text_line1'] = $options['copyright_line1'] ?? '';
        $context['copyright_text_line2'] = $options['copyright_line2'] ?? '';

        if (has_nav_menu('footer-social-menu')) {
            $footer_social_menu = Timber::get_menu('footer-social-menu');
            $context['footer_social_menu'] = wp_get_nav_menu_items($footer_social_menu->id);
        } else {
            $context['footer_social_menu'] = wp_get_nav_menu_items('Footer Social');
        }

        if (has_nav_menu('footer-primary-menu')) {
            $footer_primary_menu = Timber::get_menu('footer-primary-menu');
            $context['footer_primary_menu'] = wp_get_nav_menu_items($footer_primary_menu->id);
        } else {
            $context['footer_primary_menu'] = wp_get_nav_menu_items('Footer Primary');
        }

        if (has_nav_menu('footer-secondary-menu')) {
            $footer_secondary_menu = Timber::get_menu('footer-secondary-menu');
            $context['footer_secondary_menu'] = wp_get_nav_menu_items($footer_secondary_menu->id);
        } else {
            $context['footer_secondary_menu'] = wp_get_nav_menu_items('Footer Secondary');
        }

        // Default depth level set to 1 if not selected from admin.
        $context['p4_comments_depth'] = get_option('thread_comments_depth') ?? 1;

        $context['countries_by_initials'] = json_decode(
            file_get_contents(get_template_directory() . '/templates/countries.json'),
            true,
            512,
            \JSON_OBJECT_AS_ARRAY
        );
        // HubSpot.
        $context['hubspot_active'] = is_plugin_active('gravityformshubspot/hubspot.php');
        // The Hubspot Tracking Code snippet will add only if the user has accepted "Marketing" cookies.
        if (
            $context['hubspot_active']
            && !isset($_COOKIE['no_track']) && isset($_COOKIE['active_consent_choice'])
            && $_COOKIE['active_consent_choice'] && isset($_COOKIE['greenpeace'])
            && in_array($_COOKIE['greenpeace'], [2, 4])
        ) {
            $context['hubspot_tracking_code'] = $options['hubspot_tracking_code'] ?? '';
        }

        // Dummy thumbnail.
        $context['dummy_thumbnail'] = get_template_directory_uri() . '/images/dummy-thumbnail.png';

        // IA: Tabs menu on mobile.
        $context['mobile_tabs_menu'] = (bool) planet4_get_option('new_ia');

        // Default avatar.
        if (defined('WP_DEBUG') && WP_DEBUG === true) {
            // Gravatar throws an error for local dev default_avatar URL, so the default value is used.
            $context['default_avatar'] = 'mm'; //Mystery Man
        } else {
            $context['default_avatar'] = get_template_directory_uri() . '/images/p4-avatar.jpg';
        }

        return $context;
    }

    /**
     * Set context relating to the header
     *
     * @param array  $context To be set.
     * @param array  $page_meta_data  meta data of page.
     * @param string $post_title the title of the post.
     */
    public static function set_header(array &$context, array $page_meta_data, string $post_title): void
    {
        $meta_data_title = $page_meta_data['p4_title'] ?? '';
        $post_title_to_show = $meta_data_title ? $meta_data_title : $post_title;

        $context['header_title'] = is_front_page() ? $meta_data_title : $post_title_to_show;
        $context['header_subtitle'] = $page_meta_data['p4_subtitle'] ?? '';
        $context['header_description'] = wpautop($page_meta_data['p4_description'] ?? '');
        $context['header_button_title'] = $page_meta_data['p4_button_title'] ?? '';
        $context['header_button_link'] = $page_meta_data['p4_button_link'] ?? '';
        $context['header_button_link_checkbox'] = $page_meta_data['p4_button_link_checkbox'] ?? '';
        $context['hide_page_title'] = 'on' === ( $page_meta_data['p4_hide_page_title_checkbox'] ?? null )
            || (has_block('post-title') && !has_block('query'));
    }

    /**
     * Set context fileds relating to the background image.
     *
     * @param array $context To be set.
     */
    public static function set_background_image(array &$context): void
    {
        $background_image_id = get_post_meta(get_the_ID(), 'background_image_id', 1);
        $context['background_image'] = wp_get_attachment_url($background_image_id);
        $context['background_image_srcset'] = wp_get_attachment_image_srcset($background_image_id, 'full');
    }

    /**
     * Set open graph context fields.
     *
     * @param array  $context To be set.
     * @param object $post That the context refers to.
     */
    public static function set_og_meta_fields(array &$context, object $post): void
    {
        $context['og_title'] = $post->get_og_title();
        $context['og_description'] = $post->get_og_description();
        $context['og_image_data'] = $post->get_og_image();
    }

    /**
     * Set the context fields relating to the data layer.
     *
     * @param array $context Context to be set.
     * @param array $meta Meta data of the page.
     */
    public static function set_campaign_datalayer(array &$context, array $meta): void
    {
        $context['cf_campaign_name'] = $meta['p4_campaign_name'] ?? '';
        $context['cf_basket_name'] = $meta['p4_basket_name'] ?? '';
        $context['cf_department'] = $meta['p4_department'] ?? '';
        $context['cf_project_id'] = $meta['p4_global_project_tracking_id'] ?? 'not set';
        $context['cf_local_project'] = $meta['p4_local_project'] ?? 'not set';

        if ('not set' === $context['cf_local_project'] && !empty($meta['p4_campaign_name'])) {
            $context['cf_local_project'] = $meta['p4_campaign_name'];
        }

        $context['cf_scope'] = self::get_campaign_scope($context['cf_campaign_name']);
    }

    /**
     * Set the context fields relating to UTM.
     *
     * @param array  $context Context to be set.
     * @param object $post That the context refers to.
     */
    public static function set_utm_params(array &$context, object $post): void
    {
        $context['utm_campaign_param'] = self::parse_utm_campaign_param($context['cf_local_project']);
        $context['utm_content_param'] = '&utm_content=postid-' . $post->id;
    }

    /**
     * Parse the utm_campaign param.
     * Pass the value in lowercase and transform empty spaces into hyphens.
     * It's not needed to add if the value is equal to `not set`.
     *
     * @param string $cf_local_project It comes from meta p4_global_project_tracking_id value.
     */
    public static function parse_utm_campaign_param(string $cf_local_project): string
    {
        if ('not set' !== $cf_local_project) {
            return '&utm_campaign=' . strtolower(str_replace(' ', '-', $cf_local_project));
        }
        return '';
    }

    /**
     * Set p4_blocks datalayer value
     *
     * @param array  $context Context to be set.
     * @param object $post That the context refers to.
     */
    public static function set_p4_blocks_datalayer(array &$context, object $post): void
    {
        $post_content = $post->post_content;

        if (isset($post->articles)) {
            $post_content .= $post->articles;
        }

        if (isset($post->take_action_boxout)) {
            $post_content .= $post->take_action_boxout;
        }

        preg_match_all('/wp:planet4-blocks\/(\S+)|wp:gravityforms\/(\S+)*/', $post_content ?? '', $matches);

        $p4_blocks = array_map(
            function ($block) {
                if (str_contains($block, 'gravityforms')) {
                    $start = stripos($block, ':');
                    $end = stripos($block, '/');
                    return substr($block, $start + 1, $end - strlen($block));
                }

                return substr($block, (stripos($block, '/') + 1) - strlen($block));
            },
            array_unique($matches[0])
        );
        $context['p4_blocks'] = implode(', ', $p4_blocks);
    }

    /**
     * Set reading_time datalayer value
     * Requires milliseconds
     */
    public static function set_reading_time_datalayer(array &$context, object $post): void
    {
        $rt = $post->reading_time();
        if ($rt === null) {
            return;
        }

        $context['reading_time'] = $rt * 1000;
    }

    /**
     * Get campaign scope from value selected in the Global Projects dropdown.
     * Conditions:
     * - If Global Project equals "Local Campaign" then Scope is Local.
     * - If Global Project equals none then Scope is not set
     * - If Global Project matches any other value (apart from "Local Campaign") then Scope is Global
     *
     * @param string $global_project The Global Project value.
     * @return string The campaign scope.
     */
    private static function get_campaign_scope(string $global_project): string
    {
        switch ($global_project) {
            case 'Local Campaign':
                return 'Local';
            case 'not set':
                return 'not set';
            default:
                return 'Global';
        }
    }

    /**
     * @param array       $context   Context to be set.
     * @param array       $meta      Meta data.
     * @param string|null $post_type Post type.
     */
    public static function set_custom_styles(
        array &$context,
        array $meta,
        ?string $post_type = null
    ): void {
        if ('campaign' === $post_type) {
            $custom_styles = [
                'nav_type' => $meta['campaign_nav_type'] ?? null,
            ];

            $context['custom_styles'] = $custom_styles;
            return;
        }

        $context['custom_styles'] = [
            'nav_type' => $meta['nav_type'] ?? 'planet4',
        ];
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
}
