<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings\CommentsGdpr;

class M020MigrateCommentsSettings extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        $comments_settings = get_option('planet4_comments');
        $old_gdpr_setting = isset($comments_settings['gdpr_checkbox']) && $comments_settings['gdpr_checkbox'] === 'on';

        $options = get_option('planet4_options');
        unset($options['planet4_comments']);
        update_option('planet4_options', $options);

        update_option(CommentsGdpr::KEY, $old_gdpr_setting);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
