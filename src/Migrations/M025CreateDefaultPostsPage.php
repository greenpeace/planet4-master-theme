<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Add default for Posts page.
 */
class M025CreateDefaultPostsPage extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $existing_posts_page = get_option('page_for_posts');
        if ($existing_posts_page) {
            return;
        }

        $new_posts_page = array(
            'post_type' => 'page',
            'post_title' => 'News & Stories',
            'post_excerpt' => 'Read the latest updates.',
            'post_status' => 'publish',
        );

        $new_posts_page_id = wp_insert_post($new_posts_page);
        update_option('page_for_posts', $new_posts_page_id);

        $multilingual = is_plugin_active('sitepress-multilingual-cms/sitepress.php');
        if (!$multilingual) {
            exit();
        }

        // Get all active languages.
        $languages = apply_filters('wpml_active_languages', null, 'orderby=id&order=desc');

        foreach ($languages as $lang) {
            global $sitepress;
            $default_locale = $sitepress->get_default_language();

            $locale = $lang['language_code'];
            if ($default_locale === $locale) {
                continue;
            }

            do_action('wpml_switch_language', $locale);

            // Create one more page with same content.
            $new_posts_page_id_tr = wp_insert_post($new_posts_page);
            update_option('page_for_posts', $new_posts_page_id_tr);

            // https://wpml.org/wpml-hook/wpml_element_type/
            $wpml_element_type = apply_filters('wpml_element_type', 'page');

            // Get the language info of the original post.
            // https://wpml.org/wpml-hook/wpml_element_language_details/
            $get_language_args = array('element_id' => $new_posts_page_id, 'element_type' => 'page');
            $original_post_language_info = apply_filters('wpml_element_language_details', null, $get_language_args);

            $set_language_args = array(
                'element_id' => $new_posts_page_id_tr,
                'element_type' => $wpml_element_type, // post_page
                'trid' => $original_post_language_info->trid,
                'language_code' => $locale,
                'source_language_code' => $default_locale
            );

            do_action('wpml_set_element_language_details', $set_language_args);
        }
    }
}
