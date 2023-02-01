<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\ActionPage;

/**
 * If No Action type added, Create "Petitions" (with slug petitions) Action type and set it as the default.
 */
class M016CreateDefaultActionType extends MigrationScript
{
    private const DEFAULT_ACTION_TYPE = 'Petitions';
    private const DEFAULT_ACTION_TYPE_SLUG = 'petitions';

    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {

        // Get planet4 action type taxonomy terms.
        $action_terms = get_terms(
            [
                'fields' => 'all',
                'hide_empty' => false,
                'taxonomy' => ActionPage::TAXONOMY,
            ]
        );

        if (0 !== count($action_terms)) {
            return;
        }

        // Insert new action type term.
        $new_action_type = wp_insert_term(
            self::DEFAULT_ACTION_TYPE,
            ActionPage::TAXONOMY,
            [
                'slug' => self::DEFAULT_ACTION_TYPE_SLUG,
            ]
        );
        if (is_wp_error($new_action_type) || !isset($new_action_type['term_id'])) {
            return;
        }

        $term_id = $new_action_type['term_id'];
        // Update default action type.
        update_option('p4_default_action_type', $term_id);
    }
}
