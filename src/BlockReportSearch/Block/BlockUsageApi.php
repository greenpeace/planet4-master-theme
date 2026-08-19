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
     * Count blocks by type and style.
     *
     * If style is not specified, an empty key 'n/a' is used.
     */
    public function get_count(): array
    {
        return $this->usage->count_blocks($this->params);
    }
}
