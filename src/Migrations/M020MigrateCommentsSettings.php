<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\Settings\CommentsGdpr;

class M020MigrateCommentsSettings
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(): void
    {
        $value = planet4_get_option('gdpr_checkbox');

        $options = get_option('planet4_option');
        unset($options['planet4_comments']);
        update_option('planet4_options', $options);
        update_option(CommentsGdpr::KEY, $value === 'on');
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
