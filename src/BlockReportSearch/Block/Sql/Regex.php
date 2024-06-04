<?php

/**
 * Block search regex
 *
 * @package P4BKS\Search\Block
 */

namespace P4\MasterTheme\BlockReportSearch\Block\Sql;

use P4\MasterTheme\BlockReportSearch\Block\Query\Parameters;

/**
 * Regular expression used in SQL query
 */
class Regex
{
    private Parameters $params;

    /**
     * @param Parameters $params Query parameters.
     */
    public function __construct(Parameters $params)
    {
        $this->params = $params;
    }

    /**
     * @todo: $params->attributes not implemented.
     */
    public function __toString(): string
    {
        $block_ns = $this->params->namespace();
        $block_name = $this->params->name();

        if ('core' === $block_ns) {
            $block = '[^/]*';
        } elseif (0 === strpos($block_name, 'core/')) {
            $block = explode('/', $block_name)[1];
        } else {
            $name = $block_name ? $block_name : '.*';
            $block = $block_ns ? $block_ns . '/.*' : $name;
        }
        $attrs = $this->params->content() ? '.*' . $this->params->content() . '.*' : '.*';

        return sprintf('.*<!-- wp:%s ?%s/?-->.*', $block, $attrs);
    }
}
