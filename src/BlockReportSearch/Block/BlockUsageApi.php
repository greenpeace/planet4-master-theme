<?php

/**
 * Table displaying blocks usage
 */

namespace P4\MasterTheme\BlockReportSearch\Block;

use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters;

/**
 * Block usage API
 */
class BlockUsageApi
{
    public const DEFAULT_POST_STATUS = [ 'publish' ];

    private BlockUsage $usage;

    private Parameters $params;

    // phpcs:ignore SlevomatCodingStandard.TypeHints.PropertyTypeHint.MissingAnyTypeHint
    private $items;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->usage = new BlockUsage();
        $this->params = ( new Parameters() )
            ->with_post_status(self::DEFAULT_POST_STATUS)
            ->with_post_type(
                \get_post_types(
                    [
                        'public' => true,
                        'exclude_from_search' => false,
                    ]
                )
            );
    }

    /**
     * Count blocks by type and style
     *
     * If style is not specified, an empty key 'n/a' is used.
     */
    public function get_count(): array
    {
        if (null === $this->items) {
            $this->fetch_items();
        }

        $posts_list_block = $this->usage->filter_query_blocks($this->items, $this->usage::POSTS_LIST_NAME);
        $actions_list_block = $this->usage->filter_query_blocks($this->items, $this->usage::ACTIONS_LIST_NAME);

        // Merge updated Posts/Actions List array with items array for API
        $query_blocks = [
            'posts_list_block' => $posts_list_block,
            'actions_list_block' => $actions_list_block,
        ];

        foreach ($query_blocks as $block) {
            if (empty($block)) {
                continue;
            }

            $updated_block = $this->update_query_blocks($block);
            $this->items = array_merge($this->items, $updated_block);
        }

        $types = array_unique(
            array_column($this->items, 'block_type')
        );
        $blocks = array_fill_keys(
            $types,
            [
                'total' => 0,
                'styles' => [],
            ]
        );
        ksort($blocks);

        foreach ($this->items as $item) {
            $styles = empty($item['block_styles']) ? [ 'n/a' ] : $item['block_styles'];
            foreach ($styles as $style) {
                $type = $item['block_type'];
                if (! isset($blocks[ $type ]['styles'][ $style ])) {
                    $blocks[ $type ]['styles'][ $style ] = 0;
                }
                $blocks[ $type ]['styles'][ $style ]++;
                $blocks[ $type ]['total']++;
            }
            ksort($blocks[ $type ]['styles']);
        }

        return $blocks;
    }

    /**
     * Fetch parsed blocks
     */
    private function fetch_items(): void
    {
        $this->items = $this->usage->get_blocks($this->params);
    }

    /**
     * Function to update Query block block_type value
     * With value of Posts/Actions list namespace
     *
     * @param array $blocks     array of blocks.
     */
    private function update_query_blocks(array $blocks): array
    {
        $updated = [];

        foreach ($blocks as $key => $item) {
            if (isset($item['block_attrs']['namespace'])) {
                $item['block_type'] = $item['block_attrs']['namespace'];
            }
            $updated[$key] = $item;
        }
        return $updated;
    }
}
