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

        $sql = 'SELECT meta_key,
            SUM(meta_value <> "") as filled_field,
            SUM(meta_value = "") as empty_field,
            COUNT(post_id) as total_posts
            FROM wp_postmeta
            where meta_key in (
                "p4_campaign_name",
                "p4_local_project",
                "p4_basket_name",
                "p4_department",
                "campaign_nav_type",
                "p4_hide_page_title_checkbox",
                "p4_title",
                "p4_subtitle",
                "p4_description",
                "background_image_id",
                "background_image",
                "p4_og_title",
                "p4_og_description",
                "p4_og_image",
                "p4_og_image_id",
                "p4-seo-canonical-url",
                "p4_seo_meta_title",
                "p4_seo_meta_description",
                "nav_type",
                "p4_seo_canonical_url",
                "p4_author_override",
                "p4_button_title",
                "p4_button_link",
                "p4_button_link_checkbox"
            )
            GROUP BY meta_key;';

        $results = $wpdb->get_results($sql);

        foreach ($results as $result) {
            if($result->empty_field === $result->total_posts) {
                echo "Deleting: {$result->meta_key}" . "\n";
            } else {
                echo "Keeping:  {$result->meta_key}." . "\n";
            }
        }
    }
}
