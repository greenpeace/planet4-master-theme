<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Replace the deprecated pallete colors from content
 */
class M025ReplaceDeprecatedColorsFromContent extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     * phpcs:disable SlevomatCodingStandard.Functions.UnusedParameter -- interface implementation
     */
    protected static function execute(MigrationRecord $record): void
    {
        global $wpdb;

        $colors = array(
            [ 'from' => 'grey-80', 'to' => 'grey-900' ],
            [ 'from' => 'grey-60', 'to' => 'grey-800' ],
            [ 'from' => 'grey-40', 'to' => 'grey-600' ],
            [ 'from' => 'grey-20', 'to' => 'grey-500' ],
            [ 'from' => 'grey-10', 'to' => 'grey-200' ],
            [ 'from' => 'grey-05', 'to' => 'grey-100' ],
            [ 'from' => 'gp-green', 'to' => 'green-500' ],
            [ 'from' => 'dark-blue', 'to' => 'dark-green-800' ],
            [ 'from' => 'orange-hover', 'to' => 'green-500' ],
            [ 'from' => 'grey', 'to' => 'grey-900' ],
            [ 'from' => 'blue', 'to' => 'green-800' ],
        );

        foreach ($colors as $color) {
            $from = $color['from'];
            $to = $color['to'];

            $from_bg = sprintf("has-%s-background-color", $color['from']);
            $to_bg = sprintf("has-%s-background-color", $color['to']);
            $from_color = sprintf("has-%s-color", $color['from']);
            $to_color = sprintf("has-%s-color", $color['to']);

            echo sprintf("Replace all the occurrences of '%s' with '%s'\n", $color['from'], $color['to']);

            $sql = '
                SELECT
                    ID,
                    post_parent,
                    MAX(ID) as latest_post,
                    count(ID) as total_revisions
                FROM wp_posts
                WHERE (
                    post_content LIKE "%%%1$s%%"
                    OR post_content LIKE "%%%2$s%%"
                )
                GROUP BY post_parent
            ';

            $prepared_sql = $wpdb->prepare($sql, [ $from_bg, $from_color ]);
            $results = $wpdb->get_results($prepared_sql, ARRAY_A);

            if (count($results) <= 0) {
                continue;
            }

            foreach ($results as $post) {
                $sql = '
                    UPDATE wp_posts
                    SET post_content = REPLACE(
                        REPLACE(post_content, "%1$s", "%2$s"),
                        "%3$s",
                        "%4$s"
                    )
                    WHERE (post_content LIKE "%%%5$s%%" OR post_content LIKE "%%%6$s%%")
                    AND ID=%7$s
                ';

                $prepared_sql = $wpdb->prepare($sql, [
                    $from_bg,
                    $to_bg,
                    $from_color,
                    $to_color,
                    $from_bg,
                    $from_color,
                    $post["ID"],
                ]);
                $wpdb->query($prepared_sql);
            }
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
