<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the Dropdown navigation menus feature flag from Planet 4 Information Architecture settings.
 */
class M014RemoveDropdownNavigationMenusOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        // Dropdown navigation menus feature flag.
        $planet4_ia = get_option('planet4_ia');
        unset($planet4_ia['dropdown_menu']);
        update_option('planet4_ia', $planet4_ia);
    }
}
