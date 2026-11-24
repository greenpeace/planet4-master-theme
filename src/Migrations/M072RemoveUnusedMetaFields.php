<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the subtitle meta custom field.
 */
class M072RemoveUnusedMetaFields extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    public static function execute(MigrationRecord $record): void
    {
        global $wpdb;

        $meta_keys = [
            'p4_subtitle',
        ];

        foreach ($meta_keys as $meta_key) {
            $wpdb->query("DELETE FROM wp_postmeta WHERE meta_key='$meta_key'");
        }
    }
}
