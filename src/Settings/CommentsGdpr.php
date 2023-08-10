<?php

namespace P4\MasterTheme\Settings;

class CommentsGdpr
{
    public const KEY = 'planet4_comments_gdpr';

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
            __('Comments opt-in checkbox', 'planet4-master-theme-backend'),
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
                    'Display a mandatory opt-in checkbox in the Comments form (GDPR requirement).',
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
