<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Features;

/**
 * Remove the Theme Editor feature flag from Planet 4 features.
 */
class M012RemoveThemeEditorOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     * @return void
     */
    protected static function execute(MigrationRecord $record): void
    {
        // Theme Editor feature flag.
        $options = get_option(Features::OPTIONS_KEY);
        unset($options['theme_editor']);
        update_option(Features::OPTIONS_KEY, $options);
    }
}
