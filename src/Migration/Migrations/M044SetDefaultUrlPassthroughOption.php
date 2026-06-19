<?php

namespace P4\MasterTheme\Migration\Migrations;

use P4\MasterTheme\Migration\MigrationRecord;
use P4\MasterTheme\Migration\MigrationScript;

/**
 * Set default "Consent default: url_passthrough" option via the P4 settings.
 */
class M044SetDefaultUrlPassthroughOption extends MigrationScript
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
        $options['consent_default_url_passthrough'] = true;
        update_option('planet4_options', $options);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
