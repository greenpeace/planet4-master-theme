<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 *  Rename Twiter Footer className to X-Twitter.
 */
class M025RenameTwiterFooterClassName extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $menu_slug = 'footer-social';
        $menu = wp_get_nav_menu_object($menu_slug);

        foreach ((array) wp_get_nav_menu_items($menu->term_id) as $menu_item) {
            $menu_item_classes = $menu_item->classes[0];
            $search = 'twitter';
            if (!str_contains($menu_item_classes, $search) || str_contains($menu_item_classes, 'x-')) {
                continue;
            }

            $current_post_meta = get_post_meta($menu_item->ID, '_menu_item_classes', true);
            $current_post_meta[0] = str_replace($search, "x-" . $search, $current_post_meta[0]);
            update_post_meta($menu_item->ID, "_menu_item_classes", $current_post_meta);
        }
    }
}
