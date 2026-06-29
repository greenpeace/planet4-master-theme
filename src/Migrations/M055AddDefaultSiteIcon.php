<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove custom site icon, since we disabled the option to customize it.
 */
class M055AddDefaultSiteIcon extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $icon_path = get_template_directory() . '/favicon.ico';

        if (!file_exists($icon_path)) {
            echo 'favicon.ico not found at expected path.';
            return;
        }

        $upload = wp_upload_bits('favicon.ico', null, file_get_contents($icon_path));
        if ($upload['error']) {
            echo 'Error uploading favicon: ' . $upload['error'];
            return;
        }

        $filetype = wp_check_filetype($upload['file'], null);
        $attachment = [
            'post_mime_type' => $filetype['type'],
            'post_title' => 'Site Icon',
            'post_content' => '',
            'post_status' => 'inherit',
        ];

        $attach_id = wp_insert_attachment($attachment, $upload['file']);
        require_once ABSPATH . 'wp-admin/includes/image.php';
        $attach_data = wp_generate_attachment_metadata($attach_id, $upload['file']);
        wp_update_attachment_metadata($attach_id, $attach_data);

        update_option('site_icon', $attach_id);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
