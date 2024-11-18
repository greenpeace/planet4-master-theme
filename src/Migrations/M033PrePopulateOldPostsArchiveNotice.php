<?php

declare(strict_types=1);

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Remove the WordPress template editor option from Planet 4 Features.
 */
class M033PrePopulateOldPostsArchiveNotice extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        $cutoff = '10';
        $title = 'Oldies But Goodies Alert!';
        $description = "Hey there! Just a quick note: the stuff you're browsing through is mostly for nostalgia and archival kicks. So, before you go basing any big decisions on what you find here, maybe double-check with some fresher content."; // phpcs:ignore Generic.Files.LineLength.MaxExceeded
        $button = 'Read the latest from ' . get_bloginfo('name');
        $prefix = 'old_posts_archive_notice_';

        $options = get_option('planet4_options');

        $options[ $prefix . 'cutoff' ] = $cutoff;
        $options[ $prefix . 'title' ] = $title;
        $options[ $prefix . 'description' ] = $description;
        $options[ $prefix . 'button' ] = $button;

        update_option('planet4_options', $options);
    }
}
