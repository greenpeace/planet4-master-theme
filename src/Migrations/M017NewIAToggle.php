<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Set default New IA features toggle based on existng options.
 */
class M017NewIAToggle extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $options = get_option('planet4_options');
        $planet4_ia = get_option('planet4_ia');

        // If any of the old options was on then enable new IA toggle.
        if (
            $planet4_ia['action_post_type'] ?? null
            || $planet4_ia['list_page_pagination'] ?? null
            || $planet4_ia['mobile_tabs_menu'] ?? null
            || $planet4_ia['post_page_category_links'] ?? null
        ) {
            $options['new_ia'] = true;
        }

        update_option('planet4_options', $options);
        delete_option('planet4_ia');
    }
}
