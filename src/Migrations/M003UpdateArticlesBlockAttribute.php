<?php

namespace P4\MasterTheme\Migrations;

use P4\MasterTheme\MigrationRecord;
use P4\MasterTheme\MigrationScript;

/**
 * Update articles block attribute in block innerHTML.
 */
class M003UpdateArticlesBlockAttribute extends MigrationScript
{
    /**
     * Perform the actual migration.
     *
     * @param MigrationRecord $record Information on the execution, can be used to add logs.
     *
     */
    protected static function execute(MigrationRecord $record): void
    {
        global $wpdb;

        $post_types = [ 'page', 'campaign' ];
        $updated_post_ids = [];

        // Fetch post data having articles block from DB.
        $sql = '
			SELECT ID, post_content
			FROM %1$s
			WHERE post_type IN(' . generate_list_placeholders($post_types, 2, 's') . ')
			AND post_content REGEXP \'wp\:planet4\-blocks\/articles \{.*\"articles_description\"\:.*}\'';

        // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared
        $prepared_sql = $wpdb->prepare($sql, array_merge([ $wpdb->posts ], $post_types));
        $results = $wpdb->get_results($prepared_sql); // phpcs:ignore WordPress.DB.PreparedSQL.NotPrepared

        // Iterate posts.
        foreach ((array) $results as $post) {
            $post_content = $post->post_content;
            $updated_post_content = $post_content;
            $blocks = parse_blocks($post_content);

            // Iterate post blocks.
            foreach ($blocks as $block) {
                if ('planet4-blocks/articles' !== $block['blockName']) {
                    continue;
                }

                $article_description = $block['attrs']['articles_description'] ?? '';
                $inner_html = $block['innerHTML'];

                /**
                 * Check if articles description attribute added in the end.
                 * The last attribute ends with closing curly brace.
                 * eg.
                 * <!-- wp:planet4-blocks/articles {"articles_description":"test description"} -->
                 * <div class="wp-block-planet4-blocks-articles" data-render="planet4-blocks/articles"
                 *      data-attributes="{&quot;attributes&quot;:{...},&quot;innerBlocks&quot;:[]}"></div>
                 * <!-- /wp:planet4-blocks/articles -->
                 */
                $articles_desc_substring = '&quot;articles_description&quot;:&quot;' . $article_description . '&quot;';
                $desc_at_the_end_substring = ',' . $articles_desc_substring . '}';
                $search_article_count = ',&quot;article_count&quot;';
                $replace_article_desc = ',' . $articles_desc_substring . $search_article_count;

                if (false === strpos($inner_html, $desc_at_the_end_substring)) {
                    continue;
                }

                // Remove articles description from end of inner_HTML.
                $updated_inner_html = str_replace($desc_at_the_end_substring, '}', $inner_html);

                // Replace the articles description after article_heading and before article_count.
                $updated_inner_html = str_replace($search_article_count, $replace_article_desc, $updated_inner_html);

                // Replace updated articles block innerHTML in post content.
                $updated_post_content = str_replace($inner_html, $updated_inner_html, $updated_post_content);
            }

            // Update post content.
            if ($post_content === $updated_post_content) {
                continue;
            }

            $post_data = [
                'post_content' => $updated_post_content,
                'ID' => $post->ID,
            ];
            wp_update_post($post_data);
            $updated_post_ids[] = $post->ID;
        }

        $record->add_log(implode(',', $updated_post_ids));
        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo 'Updated post IDs:' . implode(',', $updated_post_ids);
    }
}
