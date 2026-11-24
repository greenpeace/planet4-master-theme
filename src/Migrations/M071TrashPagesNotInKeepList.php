<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Move to trash all the pages, except the ones in a list of pages to keep.
 * Trashed pages can be restored from the Pages trash.
 */
class M071TrashPagesNotInKeepList extends MigrationScript
{
    /**
     * IDs of the pages to keep.
     */
    private const PAGES_TO_KEEP = [
        675,
        683,
        693,
        713,
        715,
        717,
        727,
        729,
        739,
        741,
        745,
        747,
        749,
        751,
        753,
        1389,
        1416,
        1427,
        1429,
        1623,
        1643,
        1653,
        1662,
        1681,
        1683,
        1813,
        1815,
        1897,
        8324,
        9801,
        12239,
        12708,
        13705,
        14288,
        14436,
        14542,
        14754,
        14934,
        17927,
        18267,
        18622,
        19243,
        23371,
        23538,
        26706,
        27225,
        27424,
        27919,
        30908,
        31469,
        33450,
        34560,
        34564,
        34632,
        34654,
        34659,
        34662,
        34665,
        34668,
        34873,
        35027,
        35119,
        35890,
        36290,
        37380,
        37547,
        37687,
    ];

    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     */
    public static function execute(MigrationRecord $record): void
    {
        // Safety check: the IDs are site specific. If one of them is not a page on this site,
        // this is probably the wrong site, and every page would be trashed.
        $invalid_ids = array_filter(
            self::PAGES_TO_KEEP,
            fn (int $id) => 'page' !== get_post_type($id) || 'trash' === get_post_status($id)
        );
        if (!empty($invalid_ids)) {
            $message = 'Pages not trashed. These IDs are not pages on this site: ' . implode(', ', $invalid_ids);
            echo $message, "\n"; // phpcs:ignore
            $record->add_log($message);
            return;
        }

        $page_ids = get_posts([
            'post_type' => 'page',
            'post_status' => Utils\Constants::POST_STATUS_LIST,
            'numberposts' => -1,
            'fields' => 'ids',
            'post__not_in' => self::PAGES_TO_KEEP,
        ]);

        $trashed = [];
        foreach ($page_ids as $page_id) {
            if (wp_trash_post($page_id)) {
                $trashed[] = $page_id;
                continue;
            }

            echo 'Failed to trash page ', $page_id, "\n"; // phpcs:ignore
            $record->add_log('Failed to trash page ' . $page_id);
        }

        $message = count($trashed) . ' pages trashed: ' . implode(', ', $trashed);
        echo $message, "\n"; // phpcs:ignore
        $record->add_log($message);
    }
}
