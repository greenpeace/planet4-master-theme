<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 *  Migrate Donate button setting to a menu.
 */
class M018MigrateDonateButtonSetting extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        global $wpdb;

        $donate_menu_slug = 'donate-menu';

        $sql = 'SELECT t.term_id FROM wp_terms AS t WHERE t.slug="%s"';
        $prepared_sql = $wpdb->prepare($sql, array($donate_menu_slug));
        $results = $wpdb->get_results($prepared_sql);

        if (count($results)) {
            return;
        }

        $term = wp_insert_term(
            'Donate Menu',
            'nav_menu',
            [
                'slug' => $donate_menu_slug,
            ],
        );

        if (is_wp_error($term) || !isset($term['term_id'])) {
            return;
        }

        $locations = get_theme_mod('nav_menu_locations');
        $locations[$donate_menu_slug] = (int) $term['term_id'];
        set_theme_mod('nav_menu_locations', $locations);
    }
}
