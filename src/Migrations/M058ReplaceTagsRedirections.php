<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use Red_Item;
use Red_Group;

/**
 * Retire Tags redirection option in favor of the redirection tool.
 */
class M058ReplaceTagsRedirections extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable Squiz.PHP.DiscouragedFunctions
     */
    protected static function execute(MigrationRecord $record): void
    {
        if (!is_plugin_active('redirection/redirection.php')) {
            return;
        }

        $redirections = self::get_existing_redirections();

        if (!empty($redirections)) {
            $group_id = self::create_new_group();
            $status = self::add_redirections($group_id, $redirections);
        } else {
            $status = "No redirections were found.";
        }

        $record->add_log(print_r($status, true));
        print_r($status);
    }
    // phpcs:enable Squiz.PHP.DiscouragedFunctions

    /**
     * Gets the existing tag redirections and returns them.
     * Also, removes the the existing tag redirections to avoid conflicts with the Redirections tool.
     */
    private static function get_existing_redirections(): array
    {
        $redirections = [];

        $terms = get_terms([
            'taxonomy' => 'post_tag',
            'hide_empty' => false,
        ]);

        if (is_wp_error($terms) || empty($terms)) {
            return $redirections;
        }

        foreach ($terms as $term) {
            $redirect_page_id = get_term_meta($term->term_id, 'redirect_page', true);

            if (!empty($redirect_page_id)) {
                $source_url = "/tag/" . esc_html($term->slug);
                $target_url = get_permalink($redirect_page_id);

                $redirections[] = [
                    'source' => $source_url,
                    'target' => $target_url,
                ];
            }

            delete_term_meta($term->term_id, 'redirect_page');
        }

        return $redirections;
    }

    /**
     * Adds a new redirections group with the name "Taxonomies" if it does not exist yet.
     * Returns the group ID.
     */
    private static function create_new_group(): int
    {
        global $wpdb;

        $group_name = 'Taxonomies';
        $table_name = 'redirection_groups';
        $table = $wpdb->prefix . $table_name;

        $existing = $wpdb->get_var($wpdb->prepare(
            "SELECT id FROM $table WHERE name = %s",
            $group_name
        ));

        if ($existing) {
            return (int) $existing;
        }

        if (!class_exists('Red_Group')) {
            return 1; // Return the default group ID
        }

        $group = Red_Group::create($group_name, 1, true);

        if ($group instanceof Red_Group) {
            return (int) $group->get_id();
        }

        return 1; // Return the default group ID
    }

    /**
     * Creates the new redirections.
     * Returns the status.
     */
    private static function add_redirections(int $group_id, array $redirections): array
    {
        $status = [];

        if (!class_exists('Red_Item')) {
            $status[] = "Red_Item class does not exist.";
            return $status;
        }

        foreach ($redirections as $redirection) {
            $s = $redirection['source'];
            $t = $redirection['target'];

            $redirect = Red_Item::create([
                'url' => $s,
                'action_data' => ['url' => $t],
                'match_data' => [
                    'source' => [
                        'flag_regex' => true,
                        'flag_case' => true,
                        'flag_trailing' => true,
                        'flag_query' => 'pass',
                    ],
                ],
                'match_type' => 'url',
                'action_type' => 'url',
                'status' => 1,
                'group_id' => $group_id,
            ]);

            if ($redirect instanceof Red_Item) {
                $status[] = "Redirection successfully created from " . $s . " to " . $t;
            } else {
                $status[] = "Redirection from " . $s . " to " . $t . "failed.";
            }
        }

        return $status;
    }
}
