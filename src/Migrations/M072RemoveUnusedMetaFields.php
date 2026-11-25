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
            'p4_subtitle'
        ];

        $imploded_meta_keys = implode('","', $meta_keys);

        $sql = '
            DELETE FROM wp_postmeta
            WHERE meta_key IN ("' . $imploded_meta_keys . '")
        ';

        if ($wpdb->query($sql)) {
            echo 'Succesfully removed unused these meta fields: ' . $imploded_meta_keys . "\n";
        } else {
            echo 'Failed to remove unused these meta fields: ' . $imploded_meta_keys . "\n";
        }
    }
}
