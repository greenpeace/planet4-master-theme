<?php

namespace P4\MasterTheme\Settings;

use P4\MasterTheme\Post\ReadingTimeCalculator;

class ReadingTime
{
    public const KEY = 'planet4_reading_time_wpm';

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
            'reading',
            self::KEY,
            ['type' => 'integer', 'default' => ReadingTimeCalculator::DEFAULT_WPM]
        );

        add_settings_field(
            self::KEY,
            __('Reading time: words per minute', 'planet4-master-theme-backend'),
            [self::class, 'settings_field'],
            'reading',
            'default',
        );
    }

    public static function settings_field(): void
    {
        $value = self::get_option();
        echo '<input type="text" value="' . esc_attr($value) . '"
                     name="' . esc_attr(self::KEY) . '"
                     id="' . esc_attr(self::KEY) . '" />'
            . '<p class="description">'
            . esc_html(__(
                'Average reading words per minute (usually between 220 and 320).',
                'planet4-master-theme-backend'
            ))
            . '</p>';
    }

    public static function get_option(): int
    {
        return get_option(self::KEY, ReadingTimeCalculator::DEFAULT_WPM);
    }
}
