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
        $description = 'Displays a notice on top of old Posts.';
        $description .= '<br>';
        $description .= 'Adjust its behavior and text through';
        $description .= ' ';
        $description .= '<a href="/wp-admin/admin.php?page=planet4_settings_defaults_content" href="_self">';
        $description .= 'Defaults content settings.';
        $description .= '</a>';

        return __(
            // phpcs:ignore Generic.Files.LineLength.MaxExceeded
            $description,
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
