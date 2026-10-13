<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;
use P4\MasterTheme\Settings\Features;

/**
 * Remove the "Mandatory Image Alt Text" option from Planet 4 features.
 */
class M069RemoveMandatoryImageAltTextFeature extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $features = get_option(Features::OPTIONS_KEY);
        unset($features['mandatory_image_alt_text']);
        update_option(Features::OPTIONS_KEY, $features);
    }
}
