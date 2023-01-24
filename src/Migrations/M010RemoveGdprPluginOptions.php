<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\Features\GdprCheckbox;
use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove GDPR Comments plugin options
 */
class M010RemoveGdprPluginOptions extends MigrationScript
{
    /**
     * Disable plugin and remove plugin options.
     * Activate new option by default.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        \deactivate_plugins('gdpr-comments/gdpr-comments.php');
        \delete_option('gdpr_comments');

        GdprCheckbox::enable();
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
