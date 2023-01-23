<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Update missing media path for translated media in database(only applicable for multilingual P4 sites).
 */
class M004UpdateMissingMediaPath extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     * @return void
     */
    public static function execute(MigrationRecord $record): void
    {

        // Check if WPML plugin is active.
        if (function_exists('is_plugin_active') && ! is_plugin_active('sitepress-multilingual-cms/sitepress.php')) {
            return;
        }

        global $wpdb;

        $updated_post_ids = [];

        // Fetch attachment posts (having a sm_cloud metadata) from DB.
        $sql = '
			SELECT p.id, m.meta_value
			FROM %1$s p JOIN %2$s m ON m.post_id = p.id
			WHERE m.meta_key = "sm_cloud" AND p.post_type = "attachment"';

        $prepared_sql = $wpdb->prepare($sql, [ $wpdb->posts, $wpdb->postmeta ]); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $results = $wpdb->get_results($prepared_sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        // Iterate posts.
        foreach ((array) $results as $post) {
            $attachment_id = $post->id;
            $cloud_meta = unserialize($post->meta_value); // phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_unserialize
            $trid = apply_filters('wpml_element_trid', null, $attachment_id, 'post_attachment');
            $translations = apply_filters('wpml_get_element_translations', [], $trid, 'post_attachment');

            if ($cloud_meta) {
                foreach ($translations as $translation) {
                    $translation_post_id = $translation->element_id;

                    // Set sm_cloud field in other translations, if missing.
                    if ($translation_post_id !== $attachment_id && ! get_post_meta($translation_post_id, 'sm_cloud', true)) {
                        update_post_meta($translation_post_id, 'sm_cloud', $cloud_meta);
                        $updated_post_ids[] = $translation_post_id;
                    }
                }
            }
        }

        $record->add_log(implode(',', $updated_post_ids));
        echo 'Updated attachment IDs:' . implode(',', $updated_post_ids); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
    }
}
