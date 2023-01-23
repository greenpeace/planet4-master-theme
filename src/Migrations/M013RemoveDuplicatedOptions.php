<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove duplicated Planet 4 features comparing to options.
 */
class M013RemoveDuplicatedOptions extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     */
    protected static function execute(MigrationRecord $record): void
    {
        $features = get_option('planet4_features');
        $options = get_option('planet4_options');

        foreach (array_keys($features) as $feature) {
            if (!array_key_exists($feature, $options)) {
                continue;
            }

            unset($options[ $feature ]);
        }

        update_option('planet4_options', $options);
    }
}
