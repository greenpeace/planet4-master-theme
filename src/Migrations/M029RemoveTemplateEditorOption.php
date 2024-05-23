<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the WordPress template editor option from Planet 4 Features.
 */
class M029RemoveTemplateEditorOption extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        // Template editor feature flag.
        $features = get_option('planet4_features');
        unset($features[ 'wp_template_editor' ]);
        update_option('planet4_features', $features);
    }
}
