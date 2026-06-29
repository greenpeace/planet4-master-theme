<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Replace the deprecated pallete colors from content
 */
class M026ReplaceDeprecatedColorsFromContent extends MigrationScript
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

            $from_background_color = sprintf("has-%s-background-color", $from);
            $to_background_color = sprintf("has-%s-background-color", $to);
            $from_background_color_attr = sprintf("\"backgroundColor\":\"%s\"", $from);
            $to_background_color_attr = sprintf("\"backgroundColor\":\"%s\"", $to);
            $from_text_color = sprintf("has-%s-color", $from);
            $to_text_color = sprintf("has-%s-color", $to);
            $from_text_color_attr = sprintf("\"textColor\":\"%s\"", $from);
            $to_text_color_attr = sprintf("\"textColor\":\"%s\"", $to);

            $sql = '
                SELECT
                    ID,
                    post_type
                FROM wp_posts
                WHERE (
                    post_content LIKE "%%%1$s%%"
                    OR post_content LIKE "%%%2$s%%"
                )
                AND post_type <> "revision"
            ';

            $prepared_sql = $wpdb->prepare($sql, [ $from_background_color, $from_text_color ]);
            $results = $wpdb->get_results($prepared_sql, ARRAY_A);

            if (count($results) <= 0) {
                continue;
            }

            foreach ($results as $post) {
                $sql = '
                    UPDATE wp_posts
                    SET post_content = REPLACE(
                        REPLACE(
                            REPLACE(
                                REPLACE(post_content, "%1$s", "%2$s"),
                                "%3$s",
                                "%4$s"
                            ),
                            "%5$s",
                            "%6$s"
                        ),
                        "%7$s",
                        "%8$s"
                    )
                    WHERE (post_content LIKE "%%%9$s%%" OR post_content LIKE "%%%10$s%%")
                    AND ID=%11$s
                ';

                $prepared_sql = $wpdb->prepare($sql, [
                    $from_background_color,
                    $to_background_color,
                    $from_text_color,
                    $to_text_color,
                    $from_background_color_attr,
                    $to_background_color_attr,
                    $from_text_color_attr,
                    $to_text_color_attr,
                    $from_background_color,
                    $from_text_color,
                    $post["ID"],
                ]);

                $wpdb->query($prepared_sql);
            }
        }
    }
    // phpcs:enable SlevomatCodingStandard.Functions.UnusedParameter
}
