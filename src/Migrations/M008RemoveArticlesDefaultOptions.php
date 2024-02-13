<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings;

/**
 * Remove the "enchanced donate button" option from Planet 4 settings.
 */
class M008RemoveArticlesDefaultOptions extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $options = get_option(Settings::KEY);
        unset($options['articles_block_title']);
        unset($options['articles_block_button_title']);
        unset($options['articles_count']);
        update_option(Settings::KEY, $options);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
