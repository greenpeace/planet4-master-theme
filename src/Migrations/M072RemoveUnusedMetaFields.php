<?php

namespace P4\MasterTheme\Migrations;

use WP_CLI;
use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the meta custom fields that are not being used.
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
            'background_image_id',
            'background_image',
        ];

        $imploded_meta_keys = implode('","', $meta_keys);

        $sql = '
            DELETE FROM wp_postmeta
            WHERE meta_key IN ("' . $imploded_meta_keys . '")
        ';

        $result = $wpdb->query($wpdb->prepare($sql));

        if (false === $result) {
            WP_CLI::error('Migration failed: ' . $wpdb->last_error);
        } else {
            WP_CLI::success('Migration done, ' . $wpdb->rows_affected . ' rows were updated.');
        }
    }
}
