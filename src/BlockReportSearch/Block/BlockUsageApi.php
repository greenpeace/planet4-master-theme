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
}
