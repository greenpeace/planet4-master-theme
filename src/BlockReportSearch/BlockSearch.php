<?php

/**
 * Block search
 *
 * @package P4BKS\Search
 */

namespace P4\MasterTheme\BlockReportSearch;

use P4\MasterTheme\BlockReportSearch\Block\Query;
use P4\MasterTheme\BlockReportSearch\Block\Sql\SqlQuery;
use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters;

/**
 * Search for posts containing specific blocks
 */
class BlockSearch
{
    private Query $query;

    /**
     * @param Query|null $query implementation of Query interface.
     */
    public function __construct(?Query $query = null)
    {
        $this->query = $query ?? new SqlQuery();
    }

    /**
     * @param Parameters $params Query parameters.
     * @return int[] list of posts IDs.
     */
    public function get_posts(Parameters $params): array
    {
        return $this->query->get_posts($params);
    }

    /**
     * @param string $block_name Query parameters.
     * @return int[] list of posts IDs.
     */
    public function get_posts_with_block(string $block_name): array
    {
        return $this->get_posts(
            ( new Parameters() )->with_name($block_name)
        );
    }
}
