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
            [ 'from' => 'dark-blue', 'to' => 'p4-dark-green-800' ],
            [ 'from' => 'orange-hover', 'to' => 'green-500' ],
            [ 'from' => 'grey', 'to' => 'grey-900' ],
            [ 'from' => 'blue', 'to' => 'gp-green-800' ],
        );

        foreach ($colors as $color) {
            $from = $color['from'];
            $to = $color['to'];

            echo sprintf("Replace all the occurrences of '%s' with '%s'\n", $from, $to);

            $sql = '
                SELECT
                    ID,
                    post_parent,
                    MAX(ID) as latest_post,
                    count(ID) as total_revisions
                FROM wp_posts
                WHERE (
                    post_content LIKE "%%has-%1$s-background-color%%"
                    OR post_content LIKE "%%has-%2$s-color%%"
                )
                GROUP BY post_parent
            ';

            $prepared_sql = $wpdb->prepare($sql, [ $from, $from ]);
            $results = $wpdb->get_results($prepared_sql);

            if (count($results) <= 0) {
                continue;
            }

            $posts = json_decode(json_encode($results), true);
            foreach ($posts as $post) {
                $sql = '
                    UPDATE wp_posts
                    SET post_content = REPLACE(post_content, "%1$s", "%2$s")
                    WHERE (post_content LIKE "%has-%3$s-background-color%" OR post_content LIKE "%has-%4$s-color%")
                    AND ID=%5$s
                ';

                $prepared_sql = $wpdb->prepare($sql, [ $from, $to, $from, $from, $post["ID"]]);
                $wpdb->query($prepared_sql);
            }
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
