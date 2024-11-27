<?php

namespace P4\MasterTheme\Features;

use P4\MasterTheme\Feature;

/**
 * @see description().
 */
class OldPostsArchiveNotice extends Feature
{
    /**
     * @inheritDoc
     */
    public static function id(): string
    {
        return 'old_posts_archive_notice';
    }

    /**
     * @inheritDoc
     */
    protected static function name(): string
    {
        return __('Old Posts Archive notice', 'planet4-master-theme-backend');
    }

    /**
     * @inheritDoc
     */
    protected static function description(): string
    {
        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            'Displays a notice on top of old Posts.<br>Adjust its behavior and text through <a href="/wp-admin/admin.php?page=planet4_settings_defaults_content" href="_self">Defaults content settings.</a>',
            'planet4-master-theme-backend'
        );
    }

    /**
     * @inheritDoc
     */
    public static function show_toggle_production(): bool
    {
        return true;
    }
}
