<?php

namespace P4\MasterTheme\Settings;

class CloudflareTurnstile
{
    public const KEY = 'planet4_cloudflare_turnstile';

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
            'discussion',
            self::KEY,
            ['type' => 'boolean'],
        );

        add_settings_field(
            self::KEY,
            __('Cloudflare Turnstile', 'planet4-master-theme-backend'),
            [self::class, 'settings_field'],
            'discussion',
        );
    }

    public static function settings_field(): void
    {
        $checked = self::get_option() ? 'checked' : '';
        echo '
            <input
                type="checkbox"
                value="on"
                ' . esc_attr($checked) . '
                name="' . self::KEY . '"
                id="' . self::KEY . '"
            />
            <label for="' . self::KEY . '">'
                . __(
                    'Additional anti-spam mechanism for comments. It will show a verification option on some users.',
                    'planet4-master-theme-backend'
                ) .
            '</label>
        ';
    }

    public static function get_option(): bool
    {
        return get_option(self::KEY);
    }
}
