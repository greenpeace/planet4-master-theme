<?php

/**
 * Table displaying blocks usage
 */

namespace P4\MasterTheme\BlockReportSearch\Block;

use WP_Block_Parser;
use P4\MasterTheme\BlockReportSearch\BlockSearch;
use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters;

/**
 * Present block usage, using native WordPress table
 */
class BlockUsage
{
    public const POSTS_LIST_NAME = 'planet4-blocks/posts-list';
    public const ACTIONS_LIST_NAME = 'planet4-blocks/actions-list';

    private BlockSearch $search;

    private WP_Block_Parser $parser;

    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    private $posts_ids;

    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    private $posts;

    private array $blocks;

    /**
     * @param BlockSearch     $search Search class.
     * @param WP_Block_Parser $parser Block parser.
     */
    public function __construct(
        ?BlockSearch $search = null,
        ?WP_Block_Parser $parser = null
    ) {
        $this->search = $search ?? new BlockSearch();
        $this->parser = $parser ?? new WP_Block_Parser();
    }

    /**
     * @param Parameters $params Query parameters.
     */
    public function get_blocks(Parameters $params): array
    {
        $this->posts_ids = wp_cache_get('block_report_post_ids', 'p4-cache-blocks-report');

        if ($this->posts_ids === false) {
            $this->posts_ids = $this->search->get_posts($params);
            // Cache the data for next 24 hrs.
            wp_cache_set('block_report_post_ids', $this->posts_ids, 'p4-cache-blocks-report', DAY_IN_SECONDS);
        }

        return $this->get_filtered_blocks($this->posts_ids, $params);
    }

    /**
     * @param int[]      $posts_ids Posts IDs.
     * @param Parameters $params Query parameters.
     */
    private function get_filtered_blocks(array $posts_ids, Parameters $params): array
    {
        $this->fetch_blocks($posts_ids);
        $this->filter_blocks($params);
        $this->sort_blocks($params->order());

        return $this->blocks;
    }

    /**
     * Function to filter Query Loop block
     *
     * @param array $blocks     array of blocks.
     * @param string $block_name string for which query variation to find.
     */
    public function filter_query_blocks(array $blocks, string $block_name): array
    {
        return array_filter($blocks, fn($post) =>
            $post['block_type'] === 'core/query'
            && isset($post['block_attrs']['namespace'])
            && $post['block_attrs']['namespace'] === $block_name);
    }

    /**
     * Stream matching posts in memory-safe batches.
     *
     * Only the columns the parser needs are selected, and WP_Query's meta/term cache
     * priming is bypassed, so peak memory stays flat regardless of how many posts match.
     *
     * @param int[] $posts_ids Posts IDs.
     * @return \Generator<\WP_Post>
     */
    private function stream_posts(array $posts_ids): \Generator
    {
        global $wpdb;

        foreach (array_chunk($posts_ids, 200) as $batch) {
            $placeholders = implode(', ', array_fill(0, count($batch), '%d'));

            // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
            $rows = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT ID, post_title, post_status, post_type, post_date, post_modified, guid, post_content
                    FROM {$wpdb->posts}
                    WHERE ID IN ($placeholders)",
                    $batch
                )
            );

            foreach ($rows as $row) {
                yield new \WP_Post($row);
            }

            unset($rows);
        }
    }

    /**
     * @param int[]      $posts_ids Posts IDs.
     */
    private function fetch_blocks(array $posts_ids): void
    {
        $this->posts = [];
        $this->blocks = [];

        if (empty($posts_ids)) {
            return;
        }

        foreach ($this->stream_posts($posts_ids) as $post) {
            foreach ($this->parse_post($post) as $block) {
                $this->blocks[] = $block;
            }
        }
    }

    /**
     * Count blocks by type and style without materialising every block.
     *
     * Streams matching posts and aggregates counts on the fly, so memory usage is
     * bounded by the number of distinct block types rather than the total number of
     * blocks on the site. Query-loop variations (Posts List / Actions List) are also
     * counted under their block namespace, mirroring the report UI.
     *
     * @param Parameters $params Query parameters.
     * @return array<string, array<string, mixed>>
     */
    public function count_blocks(Parameters $params): array
    {
        $posts_ids = $this->search->get_posts($params);

        if (empty($posts_ids)) {
            return [];
        }

        $blocks = [];

        foreach ($this->stream_posts($posts_ids) as $post) {
            foreach ($this->parse_post($post) as $block) {
                $type = $block['block_type'];
                $styles = empty($block['block_styles']) ? [ 'n/a' ] : $block['block_styles'];

                $this->add_block_count($blocks, $type, $styles);

                //phpcs:ignore SlevomatCodingStandard.ControlStructures.EarlyExit.EarlyExitNotUsed
                if (
                    $type === 'core/query'
                    && isset($block['block_attrs']['namespace'])
                    && in_array(
                        $block['block_attrs']['namespace'],
                        [ self::POSTS_LIST_NAME, self::ACTIONS_LIST_NAME ],
                        true
                    )
                ) {
                    $this->add_block_count($blocks, $block['block_attrs']['namespace'], $styles);
                }
            }
        }

        ksort($blocks);
        foreach ($blocks as &$data) {
            ksort($data['styles']);
        }
        unset($data);

        return $blocks;
    }

    /**
     * Increment the running count for a block type/style bucket.
     *
     * @param array    $blocks Running totals, passed by reference.
     * @param string   $type   Block type.
     * @param string[] $styles Block styles.
     */
    private function add_block_count(array &$blocks, string $type, array $styles): void
    {
        if (! isset($blocks[$type])) {
            $blocks[$type] = [ 'total' => 0, 'styles' => [] ];
        }

        foreach ($styles as $style) {
            if (! isset($blocks[$type]['styles'][$style])) {
                $blocks[$type]['styles'][$style] = 0;
            }
            $blocks[$type]['styles'][$style]++;
            $blocks[$type]['total']++;
        }
    }

    /**
     * Filter parsed search items
     *
     * @param Parameters $params Query parameters.
     * @return array
     */
    private function filter_blocks(Parameters $params): void
    {
        if (
            empty($params->namespace())
            && empty($params->name())
            && empty($params->content())
        ) {
            return;
        }

        $filtered = $this->blocks;

        $text = $params->content();
        $filters = [
            'block_ns' => $params->namespace(),
            'block_type' => $params->name(),
            'local_name' => false !== strpos($params->name(), '/')
                ? explode('/', $params->name())[1]
                : $params->name(),
        ];

        if (! empty($filters['block_type'])) {
            if (in_array($filters['block_type'], [self::POSTS_LIST_NAME, self::ACTIONS_LIST_NAME], true)) {
                $filtered = $this->filter_query_blocks($filtered, $filters['block_type']);
            } else {
                $filtered = array_filter(
                    $filtered,
                    function ($block) use ($filters) {
                        return $block['block_type'] === $filters['block_type']
                            || $block['local_name'] === $filters['local_name'];
                    }
                );
            }
        } elseif (! empty($filters['block_ns'])) {
            $filtered = array_filter(
                $filtered,
                function ($block) use ($filters) {
                    return $block['block_ns'] === $filters['block_ns'];
                }
            );
        }

        if (! empty($text)) {
            $filtered = array_filter(
                $filtered,
                function ($block) use ($text) {
                    return strpos($block['block_type'], $text) !== false
						//phpcs:ignore WordPress.PHP.DiscouragedPHPFunctions.serialize_serialize
                        || strpos(serialize($i['block_attrs']), $text) !== false;
                }
            );
        }

        $this->blocks = $filtered;
    }

    /**
     * Sort parsed blocks
     *
     * @param array|null $sort  Sort dimensions.
     * @return array
     */
    private function sort_blocks(?array $sort = []): void
    {
        if (empty($sort)) {
            return;
        }

        $args = [];
        $block_list = $this->blocks;
        foreach ($sort as $name) {
            $args[] = array_column($block_list, $name);
            $args[] = SORT_NATURAL;
        }
        $args[] = &$block_list;

        array_multisort(...$args);

        $this->blocks = $block_list;
    }

    /**
     * Parse posts content to blocks.
     *
     * @param object $post WP_Post.
     * @return array[]
     */
    private function parse_post(\WP_Post $post): array
    {
        $output = $this->parser->parse($post->post_content);

        $block_list = array_filter(
            $output,
            function ($block) {
                return ! empty($block['blockName']);
            }
        );

        $items = [];
        while (! empty($block_list)) {
            $block = array_shift($block_list);
            $items[] = $this->format_block_data($block, $post);

            if (empty($block['innerBlocks'])) {
                continue;
            }

            $block_list = array_merge($block_list, $block['innerBlocks']);
        }

        return $items;
    }

    /**
     * Format block information.
     *
     * @param array  $block A block.
     * @param object $post  WP_Post.
     * @return array[]
     */
    private function format_block_data(array $block, \WP_Post $post): array
    {
        $type = $block['blockName'];
        $attrs = $block['attrs'] ?? [];
        $has_ns = strpos($type, '/') !== false;

        [ $namespace, $local_name ] = $has_ns ? explode('/', $type) : [ 'core', $type ];

        $classes = empty($attrs['className']) ? [] : explode(' ', $attrs['className']);
        $styles = array_filter(
            array_map(
                function ($c): ?string {
                    return 'is-style-' === substr($c, 0, 9) ? substr($c, 9) : null;
                },
                $classes
            )
        );

        return [
            'post_id' => $post->ID,
            'post_title' => $post->post_title
                ? $post->post_title : __('(no title)', 'planet4-master-theme-backend'),
            'post_status' => $post->post_status,
            'post_type' => $post->post_type,
            'post_date' => $post->post_date,
            'post_modified' => $post->post_modified,
            'post_status' => $post->post_status,
            'guid' => $post->guid,
            'block_ns' => $namespace,
            'block_type' => $type,
            'local_name' => $local_name,
            'block_attrs' => $attrs,
            'block_styles' => $styles,
        ];
    }

    /**
     * Block count in search result.
     */
    public function block_count(): int
    {
        return count($this->blocks);
    }

    /**
     * Post count in search result.
     */
    public function post_count(): int
    {
        return count($this->posts_ids);
    }
}
