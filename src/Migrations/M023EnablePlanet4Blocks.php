<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\Settings\Features;
use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Turn on the Planet4 Blocks feature.
 */
class M023EnablePlanet4Blocks extends MigrationScript
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
        $features['planet4_blocks'] = true;
        update_option(Features::OPTIONS_KEY, $features);
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
