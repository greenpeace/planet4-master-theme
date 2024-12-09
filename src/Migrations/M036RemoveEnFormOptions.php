<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the Theme Editor feature flag from Planet 4 features.
 */
class M036RemoveEnFormOptions extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        // Unset ENForm feature flag.
        $features = get_option('planet4_features');
        unset($features[ 'feature_engaging_networks' ]);
        update_option('planet4_features', $features);

        // Unser ENForm credentials.
        delete_option('p4en_main_settings');

        // Delete ENForm forms.
        $posts = get_posts([
            'post_type' => 'p4en_form',
        ]);
        foreach ($posts as $post) {
            echo 'Parsing post ', $post->ID, ': ', $post->post_title, '\n';
            wp_delete_post($post->ID, true);
        }
    }
}
