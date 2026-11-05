<?php

namespace P4\MasterTheme\Migrations\Utils;

use P4\MasterTheme\MigrationRecord;

/**
 * Replace Shortcake blocks with Planet 4 blocks, using the mappings from `ShortcakeMappings`.
 *
 * Shortcodes are replaced in classic content (freeform blocks) and in Shortcode blocks.
 * The content around a shortcode is kept in its own block.
 */
class ShortcakeMigration
{
    /**
     * Blocks that can contain shortcodes to migrate. Classic content has no block name.
     */
    private const SOURCE_BLOCKS = [null, 'core/shortcode'];

    /**
     * Migrate the posts using some Shortcake blocks.
     *
     * @param string[]             $shortcode_tags The shortcode tags to migrate, from `ShortcakeMappings`.
     * @param MigrationRecord|null $record The record to log the migration results.
     */
    public static function execute(array $shortcode_tags, ?MigrationRecord $record = null): void
    {
        $mappings = ShortcakeMappings::get($shortcode_tags);
        if (empty($mappings)) {
            return;
        }

        $posts = self::get_posts(array_keys($mappings)); print_r($posts); print_r($mappings); //exit;
        if (empty($posts)) {
            return;
        }

        echo 'Shortcake blocks migration in progress (' . implode(', ', array_keys($mappings)) . ")...\n"; // phpcs:ignore

        // Avoid the "Title is a required field" error when updating posts without a title.
        $GLOBALS['p4_skip_require_post_title'] = true;

        foreach ($posts as $post) {
            try {
                echo 'Parsing post ', $post->ID, "\n"; // phpcs:ignore
//echo "==>\n".$post->post_content;
                $new_content = self::migrate_content($post->post_content, $mappings);

                if ($post->post_content === $new_content) {
                    continue;
                }

                $result = wp_update_post(
                    wp_slash([
                        'ID' => $post->ID,
                        'post_content' => $new_content,
                    ]),
                    true
                );

                if (is_wp_error($result)) {
                    throw new \Exception($result->get_error_message()); //NOSONAR
                }

                if ($result === 0) {
                    throw new \Exception("Unknown error updating post #" . $post->ID); //NOSONAR
                }

                echo "Migration successful\n";
            } catch (\Throwable $e) {
                echo "Migration failed for post ID: ", $post->ID, "\n";
                echo $e->getMessage(), "\n";

                if ($record) {
                    $record->add_log("PID: " . $post->ID . " Error: " . $e->getMessage() . " - ");
                }
            }
        }

        unset($GLOBALS['p4_skip_require_post_title']);
    }

    /**
     * Replace the shortcodes in a post content.
     *
     * @param string $content The post content.
     * @param array  $mappings The mappings, keyed by shortcode tag.
     * @return string The new content.
     */
    public static function migrate_content(string $content, array $mappings): string
    {
        // Content without blocks is rendered with `wpautop()`, content with blocks is not.
        // Keep the classic content rendering the same once blocks are added.
        $autop = !has_blocks($content);

        $blocks = self::migrate_blocks(parse_blocks($content), $mappings, $autop, true);

        return serialize_blocks($blocks);
    }

    /**
     * Replace the shortcodes in a list of blocks, and in their inner blocks.
     *
     * @param array $blocks Parsed blocks.
     * @param array $mappings The mappings, keyed by shortcode tag.
     * @param bool  $autop Whether classic content was rendered with `wpautop()`.
     * @param bool  $top_level Whether the blocks are at the top level of the content.
     * @return array The new blocks.
     */
    private static function migrate_blocks(array $blocks, array $mappings, bool $autop, bool $top_level): array
    {
        $new_blocks = [];

        foreach ($blocks as $block) {
            if (in_array($block['blockName'], self::SOURCE_BLOCKS, true)) {
                $new_blocks = array_merge($new_blocks, self::split_block($block, $mappings, $autop, $top_level));
                continue;
            }

            if (!empty($block['innerBlocks'])) {
                $block = self::migrate_inner_blocks($block, $mappings, $autop);
            }

            $new_blocks[] = $block;
        }

        return $new_blocks;
    }

    /**
     * Replace the shortcodes in the inner blocks of a block.
     * A `null` in `innerContent` is the place of an inner block, so add one for each new block.
     *
     * @param array $block A parsed block.
     * @param array $mappings The mappings, keyed by shortcode tag.
     * @param bool  $autop Whether classic content was rendered with `wpautop()`.
     * @return array The block with its new inner blocks.
     */
    private static function migrate_inner_blocks(array $block, array $mappings, bool $autop): array
    {
        $inner_blocks = [];
        $inner_content = [];
        $index = 0;

        foreach ($block['innerContent'] as $chunk) {
            if (null !== $chunk) {
                $inner_content[] = $chunk;
                continue;
            }

            $new_blocks = self::migrate_blocks([$block['innerBlocks'][ $index++ ]], $mappings, $autop, false);
            foreach ($new_blocks as $new_block) {
                $inner_blocks[] = $new_block;
                $inner_content[] = null;
            }
        }

        $block['innerBlocks'] = $inner_blocks;
        $block['innerContent'] = $inner_content;

        return $block;
    }

    /**
     * Split a classic or Shortcode block around the shortcodes to migrate.
     *
     * @param array $block A parsed block.
     * @param array $mappings The mappings, keyed by shortcode tag.
     * @param bool  $autop Whether classic content was rendered with `wpautop()`.
     * @param bool  $top_level Whether the block is at the top level of the content.
     * @return array The blocks replacing the block.
     */
    private static function split_block(array $block, array $mappings, bool $autop, bool $top_level): array
    {
        $html = $block['innerHTML'] ?? '';
        $regex = '/' . get_shortcode_regex(array_keys($mappings)) . '/';

        if ('' === $html || !preg_match_all($regex, $html, $matches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE)) {
            return [$block];
        }

        $new_blocks = [];
        $offset = 0;

        foreach ($matches as $match) {
            // Escaped shortcode, e.g. `[[shortcake_accordion_list]]`.
            if ('[' === $match[1][0] && ']' === $match[6][0]) {
                continue;
            }

            $before = substr($html, $offset, $match[0][1] - $offset);
            $offset = $match[0][1] + strlen($match[0][0]);

            // Drop the paragraph wrapping the shortcode, if any.
            $before = preg_replace('/<p[^>]*>\s*$/i', '', $before);
            if (preg_match('/^\s*<\/p>/i', substr($html, $offset), $closing_p)) {
                $offset += strlen($closing_p[0]);
            }

            $new_blocks[] = self::content_block($block, $before, $autop);

            $tag = $match[2][0];
            $shortcode_attrs = shortcode_parse_atts($match[3][0]);
            $new_blocks[] = self::create_block($mappings[ $tag ], is_array($shortcode_attrs) ? $shortcode_attrs : []);
        }

        $new_blocks[] = self::content_block($block, substr($html, $offset), $autop);
        $new_blocks = array_values(array_filter($new_blocks));

        if (!$top_level) {
            return $new_blocks;
        }

        // Separate top level blocks with blank lines, like the editor does.
        $separated = [];
        foreach ($new_blocks as $index => $new_block) {
            if ($index > 0) {
                $separated[] = self::content_block(['blockName' => null], "\n\n", false, true);
            }
            $separated[] = $new_block;
        }

        return $separated;
    }

    /**
     * Copy of a classic or Shortcode block, with only part of its content.
     *
     * @param array  $block The original block.
     * @param string $html The content to keep.
     * @param bool   $autop Whether classic content was rendered with `wpautop()`.
     * @param bool   $keep_empty Whether to keep a block without content.
     * @return array|null The block, or null when there is no content.
     */
    private static function content_block(array $block, string $html, bool $autop, bool $keep_empty = false): ?array
    {
        if (!$keep_empty) {
            $html = trim($html);
            if ('' === $html || '' === trim(str_replace('&nbsp;', '', wp_strip_all_tags($html, true)))) {
                return null;
            }
            if ($autop && null === $block['blockName']) {
                $html = shortcode_unautop(wpautop($html));
            }
        }

        return [
            'blockName' => $block['blockName'],
            'attrs' => $block['attrs'] ?? [],
            'innerBlocks' => [],
            'innerHTML' => $html,
            'innerContent' => [$html],
        ];
    }

    /**
     * Create the block replacing a shortcode.
     *
     * @param array $mapping A mapping from `ShortcakeMappings`.
     * @param array $shortcode_attrs The shortcode attributes.
     * @return array The parsed block.
     */
    private static function create_block(array $mapping, array $shortcode_attrs): array
    {
        $attrs = ShortcakeMappings::map_attributes($mapping, $shortcode_attrs);
        $html = isset($mapping['markup']) ? call_user_func($mapping['markup'], $attrs) : '';

        return [
            'blockName' => $mapping['block'],
            'attrs' => $attrs,
            'innerBlocks' => [],
            'innerHTML' => $html,
            'innerContent' => '' === $html ? [] : [$html],
        ];
    }

    /**
     * Get the posts using some shortcodes.
     *
     * @param string[] $shortcode_tags The shortcode tags.
     * @return \WP_Post[] The posts.
     */
    private static function get_posts(array $shortcode_tags): array
    {
        global $wpdb;

        $like = implode(' OR ', array_fill(0, count($shortcode_tags), 'post_content LIKE %s'));
        $values = array_map(fn (string $tag) => '%' . $wpdb->esc_like('[' . $tag) . '%', $shortcode_tags);
        $statuses = implode(', ', array_fill(0, count(Constants::POST_STATUS_LIST), '%s'));

        // phpcs:disable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared
        $post_ids = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_status IN ($statuses) AND ($like)",
                array_merge(Constants::POST_STATUS_LIST, $values)
            )
        );
        // phpcs:enable WordPress.DB.DirectDatabaseQuery.DirectQuery,WordPress.DB.DirectDatabaseQuery.NoCaching,WordPress.DB.PreparedSQL.InterpolatedNotPrepared

        if (empty($post_ids)) {
            return [];
        }
//$post_ids = [45603];
print_r($post_ids); //exit;
        return get_posts([
            'include' => array_map('intval', $post_ids),
            'post_type' => 'any',
            'post_status' => 'any',
            'numberposts' => -1,
        ]);
    }
}
