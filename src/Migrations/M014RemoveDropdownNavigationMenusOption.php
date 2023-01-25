<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings\InformationArchitecture;

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
        $options = get_option(InformationArchitecture::OPTIONS_KEY);
        unset($options['dropdown_menu']);
        update_option(InformationArchitecture::OPTIONS_KEY, $options);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
