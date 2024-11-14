<?php

namespace P4\MasterTheme\Blocks;

use WP_Block_Parser;
use WP_Post;

class BlockList
{
    private const CACHE_GROUP = 'blocklist';
    private const CACHE_KEY = 'post-%d';

    /**
     * Check if post contains a specific block.
     *
     * @param string   $block_name Full block type to look for.
     * @param int|null $post_id    Post ID, defaults to current post.
     *
     * @return bool Whether the post content contains the specified block.
     */
    public static function has_block(string $block_name, ?int $post_id = null): bool
    {
        return in_array($block_name, self::get_block_list($post_id), true);
    }

    /**
     * Return name of blocks used in post.
     *
     * @param int|null $post_id  Post ID, defaults to current post.
     * @param array    $synced_patterns Synced Patterns already parsed.
     *
     * @return string[] List of unique block names.
     */
    public static function get_block_list(?int $post_id = null, array &$synced_patterns = []): array
    {
        if (! $post_id) {
            $post = get_post(null);
            if (! ( $post instanceof WP_Post )) {
                return [];
            }

            $post_id = $post->ID;
        }

        $found = false;
        $block_list = self::cache_get($post_id, $found);

        if (! $found || ! is_array($block_list)) {
            $post = $post ?? get_post($post_id);
            $content = $post->post_content ?? '';
            $block_list = self::parse_block_list($content, $synced_patterns, $post_id);
            self::cache_set($post_id, $block_list);
        }

        return $block_list;
    }

    /**
     * List blocks included in post.
     *
     * @param string $content Post content.
     * @param array  $seen    Synced Patterns already parsed.
     * @param int    $post_id Post ID parsed.
     *
     * @return string[] List of unique block names.
     */
    public static function parse_block_list(string $content, array &$seen = [], int $post_id = 0): array
    {
        if (! has_blocks($content)) {
            return [];
        }

        $blocks = ( new WP_Block_Parser() )->parse($content);
        $parsed = array_filter($blocks, fn ($b) => ! empty($b['blockName']));
        if (! isset($seen[ $post_id ])) {
            $seen[ $post_id ] = [];
        }

        $list = [];
        while (! empty($parsed)) {
            $block = array_shift($parsed);

            // Add synced patterns to list.
            if ('core/block' === $block['blockName'] && isset($block['attrs']['ref'])) {
                $ref_id = (int) $block['attrs']['ref'];
                if (! $ref_id) {
                    continue;
                }

                // Block pasted multiple times in same post.
                if (in_array($ref_id, $seen[ $post_id ], true)) {
                    continue;
                }

                // Block loop detection.
                // If the synced pattern is the same as the post currently parsed,
                // or if it has been parsed previously in this process,
                // this means it eventually loops back to itself.
                $seen[ $post_id ][] = $ref_id;
                if (
                    $ref_id === $post_id
                    || isset($seen[ $ref_id ])
                ) {
                    self::report_synced_pattern_loop($ref_id, $post_id, $seen);
                    continue;
                }

                $list = array_merge($list, self::get_block_list($ref_id, $seen));
            }

            if (! empty($block['innerBlocks'])) {
                $parsed = array_merge($parsed, $block['innerBlocks']);
            }

            // Add current block to list.
            if (empty($block['blockName'])) {
                continue;
            }

            $list[] = $block['blockName'];
        }

        return array_unique($list);
    }

    /**
     * @param int   $ref_id  Block ID.
     * @param int   $post_id Current post ID.
     * @param array $seen    Synced Patterns already parsed.
     */
    private static function report_synced_pattern_loop(int $ref_id, int $post_id, array $seen): void
    {
        if (! function_exists('\\Sentry\\withScope')) {
            return;
        }

        \Sentry\withScope(
            function (\Sentry\State\Scope $scope) use ($ref_id, $post_id, $seen): void {
                $scope->setContext(
                    'block loop',
                    [
                        'ref_id' => $ref_id,
                        'post_id' => $post_id,
                        'seen' => $seen,
                    ]
                );
                \Sentry\captureMessage('Synced Pattern loop');
            }
        );
    }

    /**
     * @param int  $post_id Post ID.
     * @param bool $found   Cache found.
     * @return mixed
     */
    private static function cache_get(int $post_id, bool &$found = false)
    {
        return wp_cache_get(
            sprintf(self::CACHE_KEY, $post_id),
            self::CACHE_GROUP,
            false,
            $found
        );
    }

    /**
     * @param int   $post_id Post ID.
     * @param array $data    Data.
     */
    private static function cache_set(int $post_id, array $data): bool
    {
        return wp_cache_set(
            sprintf(self::CACHE_KEY, $post_id),
            $data,
            self::CACHE_GROUP
        );
    }

    /**
     * @param int $post_id Post ID.
     */
    public static function cache_delete(int $post_id): bool
    {
        return wp_cache_delete(
            sprintf(self::CACHE_KEY, (int) $post_id),
            self::CACHE_GROUP
        );
    }
}
