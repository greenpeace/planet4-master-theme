<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the Listing Pages Background Image feature flag from Planet 4 Information Architecture settings.
 */
class M015RemoveListingPagesBackgroundImage extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        // Listing pages background image feature flag.
        $planet4_ia = get_option('planet4_ia');
        unset($planet4_ia['hide_listing_pages_background']);
        update_option('planet4_ia', $planet4_ia);
    }
}
