<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the new_identity_styles option.
 */
class M024RemoveNewIdentitySylesOption extends MigrationScript
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
        unset($options[ 'new_identity_styles' ]);
        unset($options[ 'website_navigation_style' ]);
        update_option('planet4_options', $options);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
