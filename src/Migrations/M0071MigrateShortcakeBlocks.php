<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Replace Shortcake blocks with Planet 4 blocks.
 * The mappings are configured in `Utils\ShortcakeMappings`.
 */
class M0071MigrateShortcakeBlocks extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     */
    public static function execute(MigrationRecord $record): void
    {
        Utils\ShortcakeMigration::execute(
            array_keys(Utils\ShortcakeMappings::get_all()),
            $record
        );
    }
}
