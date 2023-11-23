<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the listing_page_grid_view option.
 */
class M027RemoveListingPageGridViewOption extends MigrationScript
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
        unset($options[ 'listing_page_grid_view' ]);
        update_option('planet4_options', $options);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
