<?php

/**
 * Block search query
 */

namespace P4\MasterTheme\BlockReportSearch\Block;

/**
 * Query interface
 * Allows using SQL or Elastic implementation
 */
interface Query
{
    /**
     * Return a list of post ids
     *
     * @param Query\Parameters ...$params_list A list of parameters.
     * @return int[]
     */
    public function get_posts(Query\Parameters ...$params_list): array;
}
