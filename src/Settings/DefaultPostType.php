<?php

namespace P4\MasterTheme\Settings;

class DefaultPostType
{
    public const KEY = 'planet4_default_post_type';

    public function __construct()
    {
        $this->hooks();
    }

    public function hooks(): void
    {
        add_action('admin_init', [self::class, 'register_settings']);
    }

    public static function register_settings(): void
    {
        register_setting(
            'writing',
            self::KEY,
            ['type' => 'integer'],
        );

        add_settings_field(
            self::KEY,
            __('Default P4 Post Type', 'planet4-master-theme-backend'),
            [self::class, 'settings_field'],
            'writing',
        );
    }

    public static function settings_field(): void
    {
        wp_dropdown_categories(
            [
                'show_option_none' => __('Select Post Type', 'planet4-master-theme-backend'),
                'hide_empty' => 0,
                'orderby' => 'name',
                'selected' => self::get_option(),
                'name' => self::KEY,
                'taxonomy' => 'p4-page-type',
            ]
        );
    }

    public static function get_option(): int
    {
        return get_option(self::KEY);
    }
}
